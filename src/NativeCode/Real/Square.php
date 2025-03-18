<?php

namespace Walnut\Lang\NativeCode\Real;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Square implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntegerType || $targetType instanceof IntegerSubsetType ||
			$targetType instanceof RealType || $targetType instanceof RealSubsetType
		) {
			$minValue = $targetType->range->minValue;
			$maxValue = $targetType->range->maxValue;
			$min = $minValue === MinusInfinity::value || $minValue < 0 ? 0 : $minValue * $minValue;
			if ($maxValue !== PlusInfinity::value && $maxValue < 0) {
				$min = $maxValue * $maxValue;
			}
			$max = $maxValue === PlusInfinity::value || $minValue === MinusInfinity::value ?
				PlusInfinity::value : max($minValue * $minValue, $maxValue * $maxValue);
			return $programRegistry->typeRegistry->real($min, $max);
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry        $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$targetValue = $target;


		if ($targetValue instanceof IntegerValue || $targetValue instanceof RealValue) {
			return ($programRegistry->valueRegistry->real(
                $targetValue->literalValue * $targetValue->literalValue
			));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}