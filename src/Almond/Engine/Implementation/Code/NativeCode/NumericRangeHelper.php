<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberInterval;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberRange;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

trait NumericRangeHelper {
	use BaseType;

	private function numberBitCode(NumberIntervalEndpoint|MinusInfinity|PlusInfinity $num): int {
		return $num instanceof NumberIntervalEndpoint ? (
			match(true) {
				$num->inclusive && (string)$num->value === '0' => 0,
				$num->value >= 0 => 1,
				default => 2,
			}
		) : (
			3 * ($num === PlusInfinity::value) +
			4 * ($num === MinusInfinity::value)
		);
	}

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

	private function getResultSubsetType(
		IntegerType|RealType $targetType,
		IntegerType|RealType $parameterType,
		callable $operation,
		bool $forceRealType = false
	): IntegerSubsetType|RealSubsetType|null {
		if (
			($targetType instanceof RealSubsetType || $targetType instanceof IntegerSubsetType) &&
			($parameterType instanceof RealSubsetType || $parameterType instanceof IntegerSubsetType)
		) {
			$sumValues = [];
			foreach ($targetType->subsetValues as $targetSubsetValue) {
				foreach ($parameterType->subsetValues as $parameterSubsetValue) {
					$sumValues[] = $operation($targetSubsetValue, $parameterSubsetValue);
				}
			}
			$sumValues = array_values(array_unique($sumValues));
			return !$forceRealType &&
				$targetType instanceof IntegerSubsetType &&
				$parameterType instanceof IntegerSubsetType ?
					$this->typeRegistry->integerSubset($sumValues) :
					$this->typeRegistry->realSubset($sumValues);
		}
		return null;
	}

	private function getPlusSubsetType(
		IntegerType|RealType $targetType,
		IntegerType|RealType $parameterType
	): IntegerSubsetType|RealSubsetType|null {
		return $this->getResultSubsetType(
			$targetType, $parameterType,
			fn(Number $a, Number $b): Number => $a->add($b)
		);
	}

	private function getMinusSubsetType(
		IntegerType|RealType $targetType,
		IntegerType|RealType $parameterType
	): IntegerSubsetType|RealSubsetType|null {
		return $this->getResultSubsetType(
			$targetType, $parameterType,
			fn(Number $a, Number $b): Number => $a->sub($b)
		);
	}

	private function getMultiplySubsetType(
		IntegerType|RealType $targetType,
		IntegerType|RealType $parameterType
	): IntegerSubsetType|RealSubsetType|null {
		return $this->getResultSubsetType(
			$targetType, $parameterType,
			fn(Number $a, Number $b): Number => $a->mul($b)
		);
	}

	private function subsetWithoutZero(RealSubsetType|IntegerSubsetType $type): RealSubsetType|IntegerSubsetType {
		$values = array_filter(
			$type->subsetValues,
			fn($value): bool => (string)$value !== '0'
		);
		$numberValues = array_values($values);
		if ($type instanceof IntegerSubsetType) {
			return $this->typeRegistry->integerSubset($numberValues);
		} else {
			return $this->typeRegistry->realSubset($numberValues);
		}
	}

	private function getModuloSubsetType(
		IntegerType|RealType $targetType,
		IntegerType|RealType $parameterType
	): ResultType|IntegerSubsetType|RealSubsetType|null {
		$hasZeroParameterValue = false;
		if ($parameterType->contains(0)) {
			if ($parameterType instanceof IntegerSubsetType || $parameterType instanceof RealSubsetType) {
				if (count($parameterType->subsetValues) === 1) {
					return null;
				}
				$hasZeroParameterValue = true;
				$parameterType = $this->subsetWithoutZero($parameterType);
			}
		}
		$type = $this->getResultSubsetType(
			$targetType, $parameterType,
			fn(Number $a, Number $b): Number => $a->mod($b)
		);
		return $type && $hasZeroParameterValue ? $this->typeRegistry->result(
			$type, $this->typeRegistry->core->notANumber
		) : $type;
	}

	private function getDivideSubsetType(
		IntegerType|RealType $targetType,
		IntegerType|RealType $parameterType
	): ResultType|IntegerSubsetType|RealSubsetType|null {
		$hasZeroParameterValue = false;
		if ($parameterType->contains(0)) {
			if ($parameterType instanceof IntegerSubsetType || $parameterType instanceof RealSubsetType) {
				if (count($parameterType->subsetValues) === 1) {
					return null;
				}
				$hasZeroParameterValue = true;
				$parameterType = $this->subsetWithoutZero($parameterType);
			}
		}
		$type = $this->getResultSubsetType(
			$targetType, $parameterType,
			fn(Number $a, Number $b): Number => $a->div($b), true
		);
		return $type && $hasZeroParameterValue ? $this->typeRegistry->result(
			$type, $this->typeRegistry->core->notANumber
		) : $type;
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
			return $this->typeRegistry->integerSubset([new Number(0)]);
		}
		if ((string)$targetType->numberRange === '0') {
			return $this->typeRegistry->integerSubset([new Number(0)]);
		}
		return null;
	}

	/** @return list<NumberInterval> */
	private function getSplitInterval(
		NumberInterval $interval,
		bool $doSplit
	): array {
		if ($interval->contains(new Number(0)) && $doSplit) {
			$negativeInterval = new NumberInterval(
				$interval->start, new NumberIntervalEndpoint(new Number(0), false)
			);
			$positiveInterval = new NumberInterval(
				new NumberIntervalEndpoint(new Number(0), false), $interval->end
			);
			return [$negativeInterval, $positiveInterval];
		} else {
			return [$interval];
		}

	}

	private function getDivideRange(
		IntegerType|RealType $targetType,
		IntegerType|RealType $parameterType
	): NumberInterval {
		$tMin = $targetType->numberRange->min;
		$tMax = $targetType->numberRange->max;
		$pMin = $parameterType->numberRange->min;
		$pMax = $parameterType->numberRange->max;

		$pMinValue = $pMin === MinusInfinity::value ? -1 : $pMin->value;
		$pMaxValue = $pMax === PlusInfinity::value ? 1 : $pMax->value;

		if ($pMinValue < 0 && $pMaxValue > 0) {
			return new NumberInterval(MinusInfinity::value, PlusInfinity::value);
		}

		$hasPlusInfinity = false;
		$hasMinusInfinity = false;
		$values = [];

		foreach ([$tMin, $tMax] as $num1) {
			foreach([$pMin, $pMax] as $num2) {
				$b1 = $this->numberBitCode($num1);
				$b2 = $this->numberBitCode($num2);
				if ($b2 === 0) {
					continue;
				} elseif ($b1 === 0) {
					$values[] = new NumberIntervalEndpoint(new Number(0), true);
				} elseif ($b2 > 2) {
					$values[] = new NumberIntervalEndpoint(new Number(0), false);
				} elseif ($b1 > 2) {
					if (($b1 + $b2) % 2 === 1) {
						$hasMinusInfinity = true;
					} else {
						$hasPlusInfinity = true;
					}
				} else {
					$values[] = new NumberIntervalEndpoint(
						$num1->value->div($num2->value),
						$num1->inclusive && $num2->inclusive
					);
				};
			}
		}

		if (empty($values)) {
			return new NumberInterval(MinusInfinity::value, PlusInfinity::value);
		}

		usort($values,
			fn(NumberIntervalEndpoint $a, NumberIntervalEndpoint $b): int => $a->value <=> $b->value
		);

		// Deduplicate values: for each unique value, keep the most inclusive endpoint
		$deduplicated = [];
		foreach ($values as $value) {
			$key = (string)$value->value;
			if (!isset($deduplicated[$key]) || ($value->inclusive && !$deduplicated[$key]->inclusive)) {
				$deduplicated[$key] = $value;
			}
		}
		$values = array_values($deduplicated);

		$min = $hasMinusInfinity ? MinusInfinity::value : $values[array_key_first($values)];
		$max = $hasPlusInfinity ? PlusInfinity::value : $values[array_key_last($values)];

		return new NumberInterval($min, $max);
	}

	private function getMultiplyRange(
		IntegerType|RealType $targetType,
		IntegerType|RealType $parameterType
	): NumberInterval {
		$tMin = $targetType->numberRange->min;
		$tMax = $targetType->numberRange->max;
		$pMin = $parameterType->numberRange->min;
		$pMax = $parameterType->numberRange->max;

		$hasPlusInfinity = false;
		$hasMinusInfinity = false;
		$values = [];

		foreach ([$tMin, $tMax] as $num1) {
			foreach([$pMin, $pMax] as $num2) {
				$b1 = $this->numberBitCode($num1);
				$b2 = $this->numberBitCode($num2);
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
			fn(NumberIntervalEndpoint $a, NumberIntervalEndpoint $b): int => $a->value <=> $b->value
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

		return $this->typeRegistry->array(
			$maxLength === PlusInfinity::value || $maxLength > 0 ?
				$this->typeRegistry->integer(
					$tMin === MinusInfinity::value ? MinusInfinity::value :
						$tMin->value + ($tMin->inclusive ? 0 : 1),
					$pMax === PlusInfinity::value ? PlusInfinity::value :
						$pMax->value - ($pMax->inclusive ? 0 : 1)
				) :
				$this->typeRegistry->nothing,
			$maxLength === PlusInfinity::value ? $minLength : min($maxLength, $minLength),
			$maxLength
		);
	}

}
