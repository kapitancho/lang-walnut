<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NumericRangeHelper;

/**
 * @template TTargetType of IntegerType|RealType
 * @template TTargetValue of IntegerValue|RealValue
 * @extends NativeMethod<TTargetType, IntegerType|RealType, TTargetValue, IntegerValue|RealValue>
 */
abstract readonly class NumericBinaryDivide extends NativeMethod {
	use NumericRangeHelper;

	protected function doValidate(
		IntegerType|RealType $targetType,
		IntegerType|RealType $parameterType,
	): IntegerType|RealType|ResultType {
		if ($parameterType instanceof IntegerType && (string)$parameterType->numberRange === '1') {
			return $targetType;
		}
		$subsetType = $this->getDivideSubsetType($targetType, $parameterType);
		if ($subsetType !== null) {
			return $subsetType;
		}
		$interval = $this->getDivideRange($targetType, $parameterType);
		$intervals = $this->getSplitInterval($interval, !$targetType->contains(0));
		$real = $this->typeRegistry->realFull(...$intervals);
		return $parameterType->contains(0) ?
			$this->typeRegistry->result(
				$real,
				$this->typeRegistry->core->notANumber
			) : $real;
	}

	protected function doDivide(
		IntegerValue|RealValue $target,
		IntegerValue|RealValue $parameter
	): IntegerValue|RealValue|ErrorValue {
		if ((float)(string)$parameter->literalValue === 0.0) {
			return $this->valueRegistry->error(
				$this->valueRegistry->core->notANumber
			);
		}
		// Special case: Integer / 1 = Integer
		if (
			$parameter instanceof IntegerValue &&
			(string)$parameter->literalValue === '1'
		) {
			return $target;
		}
		return $this->valueRegistry->real(
			fdiv((float)(string)$target->literalValue, (float)(string)$parameter->literalValue)
		);
	}

}