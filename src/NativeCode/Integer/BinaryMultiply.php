<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Value\IntegerValue;

final readonly class BinaryMultiply implements NativeMethod {
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
                if ($targetType->range->minValue >= 0 && $parameterType->range->minValue >= 0) {
                    $min = $targetType->range->minValue * $parameterType->range->minValue;
                    $max = $targetType->range->maxValue === PlusInfinity::value ||
                        $parameterType->range->maxValue === PlusInfinity::value  ? PlusInfinity::value :
                        $targetType->range->maxValue * $parameterType->range->maxValue;
                    return $this->context->typeRegistry->integer($min, $max);
                }
				return $this->context->typeRegistry->integer();
			}
			if ($parameterType instanceof RealType || $parameterType instanceof RealSubsetType) {
                if ($targetType->range->minValue >= 0 && $parameterType->range->minValue >= 0) {
                    $min = $targetType->range->minValue * $parameterType->range->minValue;
                    $max = $targetType->range->maxValue === PlusInfinity::value ||
                    $parameterType->range->maxValue === PlusInfinity::value   ? PlusInfinity::value :
                        $targetType->range->maxValue * $parameterType->range->maxValue;
                    return $this->context->typeRegistry->real($min, $max);
                }
				return $this->context->typeRegistry->real();
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
		if ($targetValue instanceof IntegerValue) {
			$parameterValue = $this->toBaseValue($parameterValue);
			if ($parameterValue instanceof IntegerValue) {
	            return TypedValue::forValue($this->context->valueRegistry->integer(
					$targetValue->literalValue * $parameterValue->literalValue
	            ));
			}
			if ($parameterValue instanceof RealValue) {
	            return TypedValue::forValue($this->context->valueRegistry->real(
					$targetValue->literalValue * $parameterValue->literalValue
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