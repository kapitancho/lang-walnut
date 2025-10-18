<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class ErrorAsExternal implements NativeMethod {
	use BaseType;

	private Type $externalErrorType;

	private function externalErrorType(TypeRegistry $typeRegistry): Type {
		return $this->externalErrorType ??= $typeRegistry
			->withName(new TypeNameIdentifier("ExternalError"));
	}

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$target = $this->toBaseType($targetType);
		if ($parameterType->isSubtypeOf(
			$programRegistry->typeRegistry->union([
				$programRegistry->typeRegistry->null,
				$programRegistry->typeRegistry->string(),
			])
		)) {
			return $target instanceof ResultType ?
				$programRegistry->typeRegistry->result($target->returnType, $this->externalErrorType(
					$programRegistry->typeRegistry
				)) :
				$target;
		}
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$targetValue = $target;
		if ($targetValue instanceof ErrorValue) {
			$errorValue = $targetValue->errorValue;
			if (!($errorValue instanceof SealedValue && $errorValue->type->name->equals(
				new TypeNameIdentifier("ExternalError")
			))) {
				$parameterValue = $parameter;
				$errorMessage = $parameterValue instanceof StringValue ? $parameterValue :
					$programRegistry->valueRegistry->string('Error');

				return (
				    $programRegistry->valueRegistry->error(
						$programRegistry->valueRegistry->sealedValue(
							new TypeNameIdentifier("ExternalError"),
							$programRegistry->valueRegistry->record([
								'errorType' => $programRegistry->valueRegistry->string((string)$errorValue->type),
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