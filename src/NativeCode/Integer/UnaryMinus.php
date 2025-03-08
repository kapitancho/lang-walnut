<?php

namespace Walnut\Lang\NativeCode\Integer;

use BcMath\Number;
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
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class UnaryMinus implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntegerSubsetType) {
			return $programRegistry->typeRegistry->integerSubset(
				array_map(fn(Number $value): Number =>
					$value->mul(-1),
					$targetType->subsetValues
				)
			);
		}
		if ($targetType instanceof IntegerType) {
			return $programRegistry->typeRegistry->integer(
				$targetType->range->maxValue === PlusInfinity::value ? MinusInfinity::value :
					-$targetType->range->maxValue,
				$targetType->range->minValue === MinusInfinity::value ? PlusInfinity::value :
					-$targetType->range->minValue
			);
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

		if ($targetValue instanceof IntegerValue) {
			$target = $targetValue->literalValue;
			return TypedValue::forValue($programRegistry->valueRegistry->integer(-$target));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}