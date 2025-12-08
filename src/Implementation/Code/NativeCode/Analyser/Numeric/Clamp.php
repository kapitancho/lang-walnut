<?php

namespace Walnut\Lang\Implementation\Code\NativeCode\Analyser\Numeric;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\InvalidNumberInterval;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\NumberIntervalEndpoint as NumberIntervalEndpointInterface;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\OptionalKeyType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\AtomValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Common\Range\NumberInterval;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Value\IntegerValue;

trait Clamp {
	use BaseType;

	private function isBefore(
		MinusInfinity|PlusInfinity|NumberIntervalEndpointInterface $first,
		MinusInfinity|PlusInfinity|NumberIntervalEndpointInterface $second,
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
		MinusInfinity|PlusInfinity|NumberIntervalEndpointInterface $value,
		MinusInfinity|PlusInfinity|NumberIntervalEndpointInterface $from,
		MinusInfinity|PlusInfinity|NumberIntervalEndpointInterface $to,
	): bool {
		return $this->isBefore($from, $value) && $this->isBefore($value, $to);
	}

	private function analyseHelper(
		TypeRegistry $typeRegistry,
		IntegerType|RealType $targetType,
		Type $parameterType,
	): Type {
		$parameterType = $this->toBaseType($parameterType);
		if ($parameterType->isSubtypeOf(
			$typeRegistry->record([
				'min' => $typeRegistry->optionalKey($typeRegistry->real()),
				'max' => $typeRegistry->optionalKey($typeRegistry->real()),
			])
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
			$errorType = $typeRegistry->data(
				$isIntRange ?
					new TypeNameIdentifier('InvalidIntegerRange') :
					new TypeNameIdentifier('InvalidRealRange')
			);
			try {
				$interval = new NumberInterval($from, $to);
			} catch (InvalidNumberInterval) {
				return $typeRegistry->result($typeRegistry->nothing, $errorType);
			}

			$isInteger = $targetType instanceof IntegerType && $isIntRange;
			$numericType = $isInteger ? $typeRegistry->integerFull($interval) : $typeRegistry->realFull($interval);
			return $mayHaveError ? $typeRegistry->result($numericType, $errorType
			) : $numericType;
		}
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
	}

	private function executeHelper(
		ProgramRegistry $programRegistry,
		IntegerValue|RealValue $target,
		RecordValue $parameter
	): Value {
		$value = $target;

		$minValue = $parameter->values['min'] ?? null;
		$maxValue = $parameter->values['max'] ?? null;

		$minClamp = $minValue === null || (
			$minValue instanceof AtomValue && $minValue->type->name->equals(new TypeNameIdentifier('MinusInfinity'))
		) ? null : $minValue;
		$maxClamp = $maxValue === null || (
			$maxValue instanceof AtomValue && $maxValue->type->name->equals(new TypeNameIdentifier('PlusInfinity'))
		) ? null : $maxValue;

		// Check for invalid range
		if ($minClamp !== null && $maxClamp !== null && $minClamp->literalValue > $maxClamp->literalValue) {
			return $programRegistry->valueRegistry->error(
				$programRegistry->valueRegistry->dataValue(
					$minValue instanceof IntegerValue && $maxValue instanceof IntegerValue
						? new TypeNameIdentifier('InvalidIntegerRange')
						: new TypeNameIdentifier('InvalidRealRange'),
					$programRegistry->valueRegistry->record([
						'min' => $minValue,
						'max' => $maxValue
					])
				)
			);
		}
		$clamped = $maxClamp === null || ($maxClamp->literalValue > $value->literalValue) ? $value : $maxClamp;
		$clamped = $minClamp === null || ($minClamp->literalValue < $clamped->literalValue) ? $clamped : $minClamp;

		return $clamped;
	}

}