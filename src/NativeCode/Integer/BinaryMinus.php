<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Value\IntegerValue;

final readonly class BinaryMinus implements NativeMethod {
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

			if ($parameterType instanceof IntegerType ||
				$parameterType instanceof IntegerSubsetType ||
				$parameterType instanceof RealType ||
				$parameterType instanceof RealSubsetType
			) {
				$min = $targetType->range->minValue === MinusInfinity::value ||
					$parameterType->range->maxValue === PlusInfinity::value ? MinusInfinity::value :
					$targetType->range->minValue - $parameterType->range->maxValue;
				$max = $targetType->range->maxValue === PlusInfinity::value ||
					$parameterType->range->minValue === MinusInfinity::value ? PlusInfinity::value :
					$targetType->range->maxValue - $parameterType->range->minValue;

				if ($parameterType instanceof IntegerType || $parameterType instanceof IntegerSubsetType) {
					return $this->context->typeRegistry->integer($min, $max);
				}
				if ($parameterType instanceof RealType || $parameterType instanceof RealSubsetType) {
					return $this->context->typeRegistry->real($min, $max);
				}
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
					$targetValue->literalValue - $parameterValue->literalValue
	            ));
			}
			if ($parameterValue instanceof RealValue) {
	            return TypedValue::forValue($this->context->valueRegistry->real(
					$targetValue->literalValue - $parameterValue->literalValue
	            ));
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}