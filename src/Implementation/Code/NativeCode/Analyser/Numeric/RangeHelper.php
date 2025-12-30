<?php

namespace Walnut\Lang\Implementation\Code\NativeCode\Analyser\Numeric;

use BcMath\Number;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\NumberIntervalEndpoint as NumberIntervalEndpointInterface;
use Walnut\Lang\Blueprint\Common\Range\NumberRange;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Implementation\Common\Range\NumberInterval;
use Walnut\Lang\Implementation\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

trait RangeHelper {
	use BaseType;

	private function getMinusRange(
		IntegerType|RealType $targetType,
		IntegerType|RealType $parameterType
	): NumberInterval {
		$min =
			$targetType->numberRange->min === MinusInfinity::value ||
			$parameterType->numberRange->max === PlusInfinity::value ?
				MinusInfinity::value :
				new NumberIntervalEndpoint(
					$targetType->numberRange->min->value->sub($parameterType->numberRange->max->value),
					$targetType->numberRange->min->inclusive &&
					$parameterType->numberRange->max->inclusive
				);
		$max =
			$targetType->numberRange->max === PlusInfinity::value ||
			$parameterType->numberRange->min === MinusInfinity::value ?
				PlusInfinity::value :
				new NumberIntervalEndpoint(
					$targetType->numberRange->max->value->sub($parameterType->numberRange->min->value),
					$targetType->numberRange->max->inclusive &&
					$parameterType->numberRange->min->inclusive
				);

		return new NumberInterval($min, $max);
	}

	private function getPlusFixType(
		IntegerType|RealType $targetType,
		IntegerType|RealType $parameterType
	): IntegerType|RealType|null {
		if ((string)$parameterType->numberRange === '0') {
			return $targetType;
		}
		if ((string)$targetType->numberRange === '0') {
			return $parameterType;
		}
		return null;
	}

	private function getPlusRange(
		IntegerType|RealType $targetType,
		IntegerType|RealType $parameterType
	): NumberInterval {
		$min =
			$targetType->numberRange->min === MinusInfinity::value ||
			$parameterType->numberRange->min === MinusInfinity::value ?
				MinusInfinity::value :
				new NumberIntervalEndpoint(
					$targetType->numberRange->min->value->add($parameterType->numberRange->min->value),
					$targetType->numberRange->min->inclusive &&
					$parameterType->numberRange->min->inclusive
				);
		$max =
			$targetType->numberRange->max === PlusInfinity::value ||
			$parameterType->numberRange->max === PlusInfinity::value ?
				PlusInfinity::value :
				new NumberIntervalEndpoint(
					$targetType->numberRange->max->value->add($parameterType->numberRange->max->value),
					$targetType->numberRange->max->inclusive &&
					$parameterType->numberRange->max->inclusive
				);

		return new NumberInterval($min, $max);
	}

	private function getMultiplyFixType(
		TypeRegistry $typeRegistry,
		IntegerType|RealType $targetType,
		IntegerType|RealType $parameterType
	): IntegerType|RealType|null {
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
		return null;
	}

	private function getMultiplyRange(
		IntegerType|RealType $targetType,
		IntegerType|RealType $parameterType
	): NumberInterval|null {
		$tMin = $targetType->numberRange->min;
		$tMax = $targetType->numberRange->max;
		$pMin = $parameterType->numberRange->min;
		$pMax = $parameterType->numberRange->max;

		$hasPlusInfinity = false;
		$hasMinusInfinity = false;
		$values = [];

		$bitCode = fn(NumberIntervalEndpointInterface|MinusInfinity|PlusInfinity $num): int =>
			$num instanceof NumberIntervalEndpointInterface ? (
				match(true) {
					$num->inclusive && (string)$num->value === '0' => 0,
					$num->value >= 0 => 1,
					default => 2,
				}
			) : (
			3 * ($num === PlusInfinity::value) +
			4 * ($num === MinusInfinity::value)
		);

		foreach ([$tMin, $tMax] as $num1) {
			foreach([$pMin, $pMax] as $num2) {
				$b1 = $bitCode($num1);
				$b2 = $bitCode($num2);
				if ($b1 === 0 || $b2 === 0) {
					$values[] = new NumberIntervalEndpoint(new Number(0), true);
				} elseif ($b1 > 2 || $b2 > 2) {
					if (($b1 + $b2) % 2 === 1) {
						$hasMinusInfinity = true;
					} else {
						$hasPlusInfinity = true;
					}
				} else {
					$values[] = new NumberIntervalEndpoint(
						$num1->value->mul($num2->value),
						$num1->inclusive && $num2->inclusive
					);
				};
			}
		}
		usort($values,
			fn(NumberIntervalEndpointInterface $a, NumberIntervalEndpointInterface $b): int => $a->value <=> $b->value
		);
		$min = $hasMinusInfinity ? MinusInfinity::value : $values[array_key_first($values)];
		$max = $hasPlusInfinity ? PlusInfinity::value : $values[array_key_last($values)];
		return new NumberInterval($min, $max);
	}

	private function getSquareRange(
		IntegerType|RealType $targetType,
	): NumberInterval {
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
		return new NumberInterval(
			new NumberIntervalEndpoint($min, $minInclusive),
			$max === PlusInfinity::value ? PlusInfinity::value : new NumberIntervalEndpoint($max, $maxInclusive)
		);
	}

	private function getFromToAsArray(
		TypeRegistry $typeRegistry,
		NumberRange $from,
		NumberRange $to
	): ArrayType {
		$tMin = $to->min;
		$tMax = $to->max;
		$pMin = $from->min;
		$pMax = $from->max;

		$minLength = max(0, $tMax === PlusInfinity::value || $pMin === MinusInfinity::value ?
			0 : 1 +
				$pMin->value - ($pMin->inclusive ? 0 : 1) -
				$tMax->value + ($tMax->inclusive ? 0 : 1)
		);
		$maxLength = $pMax === PlusInfinity::value || $tMin === MinusInfinity::value ? PlusInfinity::value :
			max(0, 1 +
				$pMax->value - ($pMax->inclusive ? 0 : 1) -
				$tMin->value + ($tMin->inclusive ? 0 : 1));

		return $typeRegistry->array(
			$maxLength === PlusInfinity::value || $maxLength > 0 ?
				$typeRegistry->integer(
					$tMin === MinusInfinity::value ? MinusInfinity::value :
						$tMin->value + ($tMin->inclusive ? 0 : 1),
					$pMax === PlusInfinity::value ? PlusInfinity::value :
						$pMax->value - ($pMax->inclusive ? 0 : 1)
				) :
				$typeRegistry->nothing,
			$maxLength === PlusInfinity::value ? $minLength : min($maxLength, $minLength),
			$maxLength
		);
	}

}