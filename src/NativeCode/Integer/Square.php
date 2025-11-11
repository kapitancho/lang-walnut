<?php

namespace Walnut\Lang\NativeCode\Integer;

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
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Common\Range\NumberInterval;
use Walnut\Lang\Implementation\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Square implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntegerType ||
			$targetType instanceof RealType
		) {
			$minValue = $targetType->numberRange->min;
			$maxValue = $targetType->numberRange->max;
			$minInclusive = $minValue === MinusInfinity::value ? false : $minValue->inclusive;
			$maxInclusive = $maxValue === PlusInfinity::value ? false : $maxValue->inclusive;

			if ($minValue === MinusInfinity::value || $minValue->value < 0) {
				$min = new Number(0);
				$minInclusive = true;
			} else {
				$min = $minValue->value * $minValue->value;
			}
			if ($maxValue !== PlusInfinity::value && $maxValue->value < 0) {
				$min = $maxValue->value * $maxValue->value;
				$minInclusive = $maxInclusive;
			}
			$max = $maxValue === PlusInfinity::value || $minValue === MinusInfinity::value ?
				PlusInfinity::value : max($minValue->value * $minValue->value, $maxValue->value * $maxValue->value);
			if ($maxValue !== PlusInfinity::value && $minValue !== MinusInfinity::value &&
				$minValue->value * $minValue->value > $maxValue->value * $maxValue->value) {
				$maxInclusive = $minInclusive;
			}
			return $typeRegistry->integerFull(
				new NumberInterval(
					new NumberIntervalEndpoint($min, $minInclusive),
					$max === PlusInfinity::value ? PlusInfinity::value : new NumberIntervalEndpoint($max, $maxInclusive)
				)
			);
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

		if ($target instanceof IntegerValue) {
			return $programRegistry->valueRegistry->integer(
                $target->literalValue * $target->literalValue
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}