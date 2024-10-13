<?php

namespace Walnut\Lang\NativeCode\File;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class ReplaceContent implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		if ($targetType instanceof SealedType && $targetType->name()->equals(
			new TypeNameIdentifier('File')
		)) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof StringType || $parameterType instanceof StringSubsetType) {
				return $this->context->typeRegistry()->result(
					$this->context->typeRegistry()->string(),
					$this->context->typeRegistry()->withName(
						new TypeNameIdentifier('CannotWriteFile')
					)
				);
			}
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
		$parameterValue = $parameter->value;
		
		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof SealedValue && $targetValue->type()->name()->equals(
			new TypeNameIdentifier('File')
		)) {
			$parameterValue = $this->toBaseValue($parameterValue);
			if ($parameterValue instanceof StringValue) {
				$path = $targetValue->value()->valueOf('path')->literalValue();
				$result = @file_put_contents($path, $parameterValue->literalValue());
				if ($result === false) {
					return TypedValue::forValue($this->context->valueRegistry()->error(
						$this->context->valueRegistry()->sealedValue(
							new TypeNameIdentifier('CannotWriteFile'),
							$targetValue
						)
					));
				}
				return TypedValue::forValue($this->context->valueRegistry()->string($parameterValue));
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}