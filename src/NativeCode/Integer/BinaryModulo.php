<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class BinaryModulo implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntegerType || $targetType instanceof IntegerSubsetType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof IntegerType || $parameterType instanceof IntegerSubsetType) {
				return $this->context->typeRegistry->integer();
			}
			// @codeCoverageIgnoreStart
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
			// @codeCoverageIgnoreEnd
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
		$parameterValue = $this->toBaseValue($parameterValue);
		if ($targetValue instanceof IntegerValue) {
			$parameterValue = $this->toBaseValue($parameterValue);
			if ($parameterValue instanceof IntegerValue) {
				if ($parameterValue->literalValue === 0) {
					return TypedValue::forValue($this->context->valueRegistry->error(
						$this->context->valueRegistry->atom(new TypeNameIdentifier('NotANumber'))
					));
				}
                return TypedValue::forValue($this->context->valueRegistry->integer(
	                $targetValue->literalValue % $parameterValue->literalValue
                ));
			}
			if ($parameterValue instanceof RealValue) {
				if ((float)$parameterValue->literalValue === 0.0) {
					return TypedValue::forValue($this->context->valueRegistry->error(
						$this->context->valueRegistry->atom(new TypeNameIdentifier('NotANumber'))
					));
				}
                return TypedValue::forValue($this->context->valueRegistry->real(
	                fmod($targetValue->literalValue, $parameterValue->literalValue)
                ));
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