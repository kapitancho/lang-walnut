<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalKeyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\CoreType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\AtomValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\InvalidNumberInterval;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberInterval;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\OptionalKeyType as OptionalKeyTypeImpl;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;

trait NumericClamp {
	use BaseType;

	private function isBefore(
		MinusInfinity|PlusInfinity|NumberIntervalEndpoint $first,
		MinusInfinity|PlusInfinity|NumberIntervalEndpoint $second,
	): bool {
		if ($first instanceof MinusInfinity || $second instanceof PlusInfinity) {
			return true;
		}
		if ($first instanceof PlusInfinity || $second instanceof MinusInfinity) {
			return false;
		}
		return $first->value <= $second->value;
	}

	private function isBetween(
		MinusInfinity|PlusInfinity|NumberIntervalEndpoint $value,
		MinusInfinity|PlusInfinity|NumberIntervalEndpoint $from,
		MinusInfinity|PlusInfinity|NumberIntervalEndpoint $to,
	): bool {
		return $this->isBefore($from, $value) && $this->isBefore($value, $to);
	}

	private function validateHelper(
		IntegerType|RealType $targetType,
		Type $parameterType,
		Expression|null $origin,
	): ValidationSuccess|ValidationFailure {
		$parameterType = $this->toBaseType($parameterType);
		if ($parameterType->isSubtypeOf(
			$this->typeRegistry->record([
				'min' => new OptionalKeyTypeImpl($this->typeRegistry->real()),
				'max' => new OptionalKeyTypeImpl($this->typeRegistry->real()),
			], null)
		)) {
			$minType = $parameterType->types['min'] ?? null;
			if ($minType !== null) {
				$minType = $this->toBaseType($minType);
			}
			$maxType = $parameterType->types['max'] ?? null;
			if ($maxType !== null) {
				$maxType = $this->toBaseType($maxType);
			}

			$minFrom = $minType === null || $minType instanceof OptionalKeyType ? MinusInfinity::value : $minType->numberRange->min;
			$numFrom = $targetType->numberRange->min;
			$maxFrom = match(true) {
				$maxType === null => $numFrom,
				$maxType instanceof OptionalKeyType => $this->toBaseType($maxType->valueType)->numberRange->min,
				default => $maxType->numberRange->min
			};

			$maxTo = $maxType === null || $maxType instanceof OptionalKeyType ? PlusInfinity::value : $maxType->numberRange->max;
			$numTo = $targetType->numberRange->max;
			$minTo = match(true) {
				$minType === null => $numTo,
				$minType instanceof OptionalKeyType => $this->toBaseType($minType->valueType)->numberRange->max,
				default => $minType->numberRange->max
			};

			$from = match(true) {
				$this->isBetween($numFrom, $minFrom, $maxFrom) => $numFrom,
				$this->isBetween($maxFrom, $minFrom, $numFrom) => $maxFrom,
				default => $minFrom
			};
			$to = match(true) {
				$this->isBetween($numTo, $minTo, $maxTo) => $numTo,
				$this->isBetween($minTo, $numTo, $maxTo) => $minTo,
				default => $maxTo
			};
			if ($minType instanceof OptionalKeyType) {
				$minType = $this->toBaseType($minType->valueType);
			}
			if ($maxType instanceof OptionalKeyType) {
				$maxType = $this->toBaseType($maxType->valueType);
			}
			$mayHaveError = false;
			if (
				($minType instanceof IntegerType || $minType instanceof RealType) &&
				($maxType instanceof IntegerType || $maxType instanceof RealType)
			) {
				if (
					$minType->numberRange->max !== PlusInfinity::value &&
					$maxType->numberRange->min !== MinusInfinity::value &&
					$minType->numberRange->max->value > $maxType->numberRange->min->value
				) {
					$mayHaveError = true;
				}
			}

			$isIntRange = $minType instanceof IntegerType && $maxType instanceof IntegerType;
			$errorType = $isIntRange ? $this->typeRegistry->core->invalidIntegerRange : $this->typeRegistry->core->invalidRealRange;
			try {
				$interval = new NumberInterval($from, $to);
			} catch (InvalidNumberInterval) {
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->result($this->typeRegistry->nothing, $errorType)
				);
			}

			$isInteger = $targetType instanceof IntegerType && $isIntRange;
			$numericType = $isInteger ? $this->typeRegistry->integerFull($interval) : $this->typeRegistry->realFull($interval);
			return $this->validationFactory->validationSuccess(
				$mayHaveError ? $this->typeRegistry->result($numericType, $errorType) : $numericType
			);
		}
		return $this->validationFactory->error(
			ValidationErrorType::invalidParameterType,
			sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
			$origin
		);
	}

	private function executeHelper(
		IntegerValue|RealValue $target,
		RecordValue $parameter
	): Value {
		$value = $target;

		$minValue = $parameter->values['min'] ?? null;
		$maxValue = $parameter->values['max'] ?? null;

		$minClamp = $minValue === null || (
			$minValue instanceof AtomValue && $minValue->type->name->equals(CoreType::MinusInfinity->typeName())
		) ? null : $minValue;
		$maxClamp = $maxValue === null || (
			$maxValue instanceof AtomValue && $maxValue->type->name->equals(CoreType::PlusInfinity->typeName())
		) ? null : $maxValue;

		// Check for invalid range
		if ($minClamp !== null && $maxClamp !== null && $minClamp->literalValue > $maxClamp->literalValue) {
			$rec = $this->valueRegistry->record([
				'min' => $minValue,
				'max' => $maxValue
			]);
			return $this->valueRegistry->error(
				$minValue instanceof IntegerValue && $maxValue instanceof IntegerValue ?
					$this->valueRegistry->core->invalidIntegerRange($rec) :
					$this->valueRegistry->core->invalidRealRange($rec)
			);
		}
		$clamped = $maxClamp === null || ($maxClamp->literalValue > $value->literalValue) ? $value : $maxClamp;
		$clamped = $minClamp === null || ($minClamp->literalValue < $clamped->literalValue) ? $clamped : $minClamp;

		return $clamped;
	}

}
