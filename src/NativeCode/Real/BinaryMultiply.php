<?php

namespace Walnut\Lang\NativeCode\Real;

use BcMath\Number;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\NumberIntervalEndpoint as NumberIntervalEndpointInterface;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Common\Range\NumberInterval;
use Walnut\Lang\Implementation\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Value\IntegerValue;

final readonly class BinaryMultiply implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof RealType) {
			$parameterType = $this->toBaseType($parameterType);

			if ($parameterType instanceof IntegerType || $parameterType instanceof RealType) {
				if ((string)$parameterType->numberRange === '1') {
					return $targetType;
				}
				if ((string)$targetType->numberRange === '1') {
					return $parameterType;
				}
				if ((string)$parameterType->numberRange === '0') {
					return $typeRegistry->integerSubset([new Number(0)]);
				}
				if ((string)$targetType->numberRange === '0') {
					return $typeRegistry->integerSubset([new Number(0)]);
				}

				if (
					$targetType->numberRange->min instanceof NumberIntervalEndpointInterface && $targetType->numberRange->min->value >= 0 &&
					$parameterType->numberRange->min instanceof NumberIntervalEndpointInterface && $parameterType->numberRange->min->value >= 0
				) {
					$min = new NumberIntervalEndpoint(
						$targetType->numberRange->min->value->mul($parameterType->numberRange->min->value),
						$targetType->numberRange->min->inclusive && $parameterType->numberRange->min->inclusive
					);
					$max =
						$targetType->numberRange->max === PlusInfinity::value ||
						$parameterType->numberRange->max === PlusInfinity::value ?
							PlusInfinity::value :
							new NumberIntervalEndpoint(
								$targetType->numberRange->max->value->mul($parameterType->numberRange->max->value),
								$targetType->numberRange->max->inclusive && $parameterType->numberRange->max->inclusive
							);
					$interval = new NumberInterval($min, $max);
					return $typeRegistry->realFull($interval);
				}
				$containsZero = $targetType->contains(0) || $parameterType->contains(0);
				return $containsZero ?
					$typeRegistry->real() :
					$typeRegistry->nonZeroReal();
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$targetValue = $target;
		$parameterValue = $parameter;
		

		if ($targetValue instanceof RealValue || $targetValue instanceof IntegerValue) {
			if ($parameterValue instanceof IntegerValue || $parameterValue instanceof RealValue) {
                return $programRegistry->valueRegistry->real(
	                $targetValue->literalValue * $parameterValue->literalValue
                );
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