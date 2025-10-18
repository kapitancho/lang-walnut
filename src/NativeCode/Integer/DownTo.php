<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
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
		if ($targetType instanceof IntegerType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof IntegerType) {
				$tMin = $targetType->numberRange->min;
				$tMax = $targetType->numberRange->max;
				$pMin = $parameterType->numberRange->min;
				$pMax = $parameterType->numberRange->max;

				$minLength = max(0, $pMax === PlusInfinity::value || $tMin === MinusInfinity::value ?
					0 : 1 +
						$tMin->value - ($tMin->inclusive ? 0 : 1) -
						$pMax->value + ($pMax->inclusive ? 0 : 1)
				);
				$maxLength = $tMax === PlusInfinity::value || $pMin === MinusInfinity::value ? PlusInfinity::value :
					max(0, 1 +
						$tMax->value - ($tMax->inclusive ? 0 : 1) -
						$pMin->value + ($pMin->inclusive ? 0 : 1));

				return $programRegistry->typeRegistry->array(
					$maxLength === PlusInfinity::value || $maxLength > 0 ?
						$programRegistry->typeRegistry->integer(
							$tMin === MinusInfinity::value ? MinusInfinity::value :
								$pMin->value + ($pMin->inclusive ? 0 : 1),
							$pMax === PlusInfinity::value ? PlusInfinity::value :
								$tMax->value - ($tMax->inclusive ? 0 : 1)
						) :
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
		Value $target,
		Value $parameter
	): Value {
		$targetValue = $target;
		$parameterValue = $parameter;
		
		if ($targetValue instanceof IntegerValue) {
			if ($parameterValue instanceof IntegerValue) {
	            return ($programRegistry->valueRegistry->tuple(
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