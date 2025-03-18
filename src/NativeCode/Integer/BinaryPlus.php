<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Value\IntegerValue;

final readonly class BinaryPlus implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType
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
					$parameterType->range->minValue === MinusInfinity::value ? MinusInfinity::value :
					$targetType->range->minValue + $parameterType->range->minValue;
				$max = $targetType->range->maxValue === PlusInfinity::value ||
					$parameterType->range->maxValue === PlusInfinity::value ? PlusInfinity::value :
					$targetType->range->maxValue + $parameterType->range->maxValue;

				if ($parameterType instanceof IntegerType || $parameterType instanceof IntegerSubsetType) {
					return $programRegistry->typeRegistry->integer($min, $max);
				}
				if ($parameterType instanceof RealType || $parameterType instanceof RealSubsetType) {
					return $programRegistry->typeRegistry->real($min, $max);
				}
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry        $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$targetValue = $target;
		$parameterValue = $parameter;
		
		if ($targetValue instanceof IntegerValue) {
			if ($parameterValue instanceof IntegerValue) {
	            return ($programRegistry->valueRegistry->integer(
					$targetValue->literalValue + $parameterValue->literalValue
	            ));
			}
			if ($parameterValue instanceof RealValue) {
	            return ($programRegistry->valueRegistry->real(
					$targetValue->literalValue + $parameterValue->literalValue
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