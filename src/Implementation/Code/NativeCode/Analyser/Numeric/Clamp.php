<?php

namespace Walnut\Lang\Implementation\Code\NativeCode\Analyser\Numeric;

use BcMath\Number;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\NumberIntervalEndpoint as NumberIntervalEndpointInterface;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
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
use Walnut\Lang\Implementation\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Value\IntegerValue;

trait Clamp {
	use BaseType;

	private function analyseHelper(
		TypeRegistry $typeRegistry,
		Type $targetType,
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
			if ($minType instanceof OptionalKeyType) {
				$minType = $this->toBaseType($minType->valueType);
			}
			$maxType = $parameterType->types['max'] ?? null;
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
			$isInteger = $targetType instanceof IntegerType && $isIntRange;
			$min = $minType->numberRange->min === MinusInfinity::value || $maxType->numberRange->min === MinusInfinity::value ?
				MinusInfinity::value :
				new Number(min($minType->numberRange->min->value, $maxType->numberRange->min->value));
			$max = $minType->numberRange->max === PlusInfinity::value || $maxType->numberRange->max === PlusInfinity::value ?
				PlusInfinity::value :
				new Number(max($minType->numberRange->max->value, $maxType->numberRange->max->value));
			$numericType = $isInteger ? $typeRegistry->integer($min, $max) : $typeRegistry->real($min, $max);
			return $mayHaveError ? $typeRegistry->result($numericType,
				$typeRegistry->data(
					$isIntRange ?
						new TypeNameIdentifier('InvalidIntegerRange') :
						new TypeNameIdentifier('InvalidRealRange')
				)
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