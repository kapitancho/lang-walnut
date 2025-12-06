<?php

namespace Walnut\Lang\NativeCode\Real;

use BcMath\Number;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Common\Range\NumberInterval;
use Walnut\Lang\Implementation\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Frac implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntegerType || $targetType instanceof RealType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof NullType) {
				if ($targetType instanceof IntegerType) {
					return $typeRegistry->integerSubset([new Number(0)]);
				}
				if ($targetType instanceof RealSubsetType) {
					return $typeRegistry->realSubset(
						array_values(
							array_unique(
								array_map(
									$this->calculateFrac(...),
									$targetType->subsetValues
								)
							)
						)
					);
				}
				$min = $targetType->numberRange->min;
				$max = $targetType->numberRange->max;

				return $typeRegistry->realFull(
					new NumberInterval(
						match(true) {
							$min === MinusInfinity::value || $min->value <= -1 => new NumberIntervalEndpoint(new Number(-1), false),
							$min->value < 1 => $min,
							default => new NumberIntervalEndpoint(new Number(0), true),
						},
						match(true) {
							$max === PlusInfinity::value || $max->value >= 1 => new NumberIntervalEndpoint(new Number(1), false),
							$max->value > -1 => $max,
							default => new NumberIntervalEndpoint(new Number(0), true),
						}
					),
				);
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	private function calculateFrac(Number $value): Number {
		if ($value > 0) {
			return $value->sub($value->floor());
		} elseif ($value < 0) {
			return $value->sub($value->ceil());
		} else {
			return new Number(0);
		}
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		if ($target instanceof RealValue || $target instanceof IntegerValue) {
			return $programRegistry->valueRegistry->real($this->calculateFrac($target->literalValue));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}
