<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class ErrorAsExternal implements NativeMethod {
	use BaseType;

	private Type $externalErrorType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	private function externalErrorType(): Type {
		return $this->externalErrorType ??= $this->context->typeRegistry
			->withName(new TypeNameIdentifier("ExternalError"));
	}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$target = $this->toBaseType($targetType);
		if ($parameterType->isSubtypeOf(
			$this->context->typeRegistry->union([
				$this->context->typeRegistry->null,
				$this->context->typeRegistry->string(),
			])
		)) {
			return $target instanceof ResultType ?
				$this->context->typeRegistry->result($target->returnType, $this->externalErrorType()) :
				$target;
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		if ($targetValue instanceof ErrorValue) {
			$errorValue = $targetValue->errorValue;
			if (!($errorValue instanceof SealedValue && $errorValue->type->name->equals(
				new TypeNameIdentifier("ExternalError")
			))) {
				$parameterValue = $parameter->value;
				$errorMessage = $parameterValue instanceof StringValue ? $parameterValue :
					$this->context->valueRegistry->string('Error');
				return new TypedValue(
					$this->context->typeRegistry->result(
						$this->context->typeRegistry->nothing,
						$this->externalErrorType()
					),
					$this->context->valueRegistry->error(
						$this->context->valueRegistry->sealedValue(
							new TypeNameIdentifier("ExternalError"),
							$this->context->valueRegistry->record([
								'errorType' => $this->context->valueRegistry->string((string)$errorValue->type),
								'originalError' => $targetValue,
								'errorMessage' => $errorMessage
							])
						)
					)
				);
			}
		}
		return $target;
	}

}