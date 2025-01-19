<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Value\IntegerValue;

final readonly class DownTo implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntegerType || $targetType instanceof IntegerSubsetType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof IntegerType || $parameterType instanceof IntegerSubsetType) {
				$tMin = $targetType->range->minValue;
				$tMax = $targetType->range->maxValue;
				$pMin = $parameterType->range->minValue;
				$pMax = $parameterType->range->maxValue;

				$minLength = max(0, $pMax === PlusInfinity::value || $tMin === MinusInfinity::value ?
					0 : 1 + $tMin - $pMax
				);
				$maxLength = $tMax === PlusInfinity::value || $pMin === MinusInfinity::value ? PlusInfinity::value :
					max(0, 1 + $tMax - $pMin);

				return $programRegistry->typeRegistry->array(
					$maxLength === PlusInfinity::value || $maxLength > 0 ?
						$programRegistry->typeRegistry->integer($pMin, $tMax) :
						$programRegistry->typeRegistry->nothing,
					$maxLength === PlusInfinity::value ? $minLength : min($maxLength, $minLength),
					$maxLength
				);
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry $programRegistry,
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		$parameterValue = $parameter->value;
		
		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof IntegerValue) {
			$parameterValue = $this->toBaseValue($parameterValue);
			if ($parameterValue instanceof IntegerValue) {
	            return TypedValue::forValue($programRegistry->valueRegistry->tuple(
		            $targetValue->literalValue > $parameterValue->literalValue  ?
						array_map(fn(int $i): IntegerValue =>
							$programRegistry->valueRegistry->integer($i),
							range($targetValue->literalValue, $parameterValue->literalValue, -1)
						) : []
	            ));
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}