<?php

namespace Walnut\Lang\NativeCode\File;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Content implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		if ($targetType instanceof SealedType && $targetType->name->equals(
			new TypeNameIdentifier('File')
		)) {
			return $this->context->typeRegistry->result(
				$this->context->typeRegistry->string(),
				$this->context->typeRegistry->withName(
					new TypeNameIdentifier('CannotReadFile')
				)
			);
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

		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof SealedValue && $targetValue->type->name->equals(
			new TypeNameIdentifier('File')
		)) {
			$path = $targetValue->value->valueOf('path')->literalValue;
			$contents = @file_get_contents($path);
			if ($contents === false) {
				return TypedValue::forValue($this->context->valueRegistry->error(
					$this->context->valueRegistry->sealedValue(
						new TypeNameIdentifier('CannotReadFile'),
						$targetValue->value
					)
				));
			}
			return TypedValue::forValue($this->context->valueRegistry->string($contents));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}