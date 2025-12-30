<?php

namespace Walnut\Lang\NativeCode\Real;

use BcMath\Number;
use RoundingMode;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\NumberInterval as NumberIntervalInterface;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Common\Range\NumberInterval;
use Walnut\Lang\Implementation\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class AsInteger implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntegerSubsetType || $targetType instanceof RealSubsetType) {
			return $typeRegistry->integerSubset(
				array_map(fn(Number $v) => $v > 0 ? $v->floor() : $v->ceil(),
					$targetType->subsetValues)
			);
		}
		if ($targetType instanceof IntegerType || $targetType instanceof RealType) {
			return $typeRegistry->integerFull(... array_map(
				fn(NumberIntervalInterface $interval) => new NumberInterval(
					$interval->start === MinusInfinity::value ? MinusInfinity::value :
						new NumberIntervalEndpoint(
							$interval->start->value->round(0, RoundingMode::TowardsZero),
							(string)$interval->start->value !== (string)$interval->start->value->round(0, RoundingMode::TowardsZero) ||
							$interval->start->inclusive
						),
					$interval->end === PlusInfinity::value ? PlusInfinity::value :
						new NumberIntervalEndpoint(
							$interval->end->value->round(0, RoundingMode::TowardsZero),
							(string)$interval->end->value !== (string)$interval->end->value->round(0, RoundingMode::TowardsZero) ||
							$interval->end->inclusive
						)
				),
				$targetType->numberRange->intervals
			));
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
		if ($target instanceof RealValue || $target instanceof IntegerValue) {
			return $programRegistry->valueRegistry->integer(
				$target->literalValue->round(0, RoundingMode::TowardsZero)
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}