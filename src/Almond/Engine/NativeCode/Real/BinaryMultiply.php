<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Real;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NumericRangeHelper;

/** @extends NativeMethod<IntegerType|RealType, IntegerType|RealType, IntegerValue|RealValue, IntegerValue|RealValue> */
final readonly class BinaryMultiply extends NativeMethod {
	use NumericRangeHelper;

	protected function getValidator(): callable {
		return function(IntegerType|RealType $targetType, IntegerType|RealType $parameterType): Type {
			$fixType = $this->getMultiplyFixType($targetType, $parameterType);
			if ($fixType !== null) {
				return $fixType;
			}
			$subsetType = $this->getMultiplySubsetType(
				$targetType, $parameterType
			);
			if ($subsetType !== null) {
				return $subsetType;
			}

			$interval = $this->getMultiplyRange($targetType, $parameterType);
			$intervals = $this->getSplitInterval(
				$interval,
				!$targetType->contains(0) && !$parameterType->contains(0)
			);
			return $this->typeRegistry->realFull(... $intervals);
		};
	}

	protected function getExecutor(): callable {
		return fn(IntegerValue|RealValue $target, IntegerValue|RealValue $parameter): RealValue =>
			$this->valueRegistry->real(
				$target->literalValue * $parameter->literalValue
			);
	}
}
