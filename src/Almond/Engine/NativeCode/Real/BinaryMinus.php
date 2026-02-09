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
final readonly class BinaryMinus extends NativeMethod {
	use NumericRangeHelper;

	protected function getValidator(): callable {
		return function(IntegerType|RealType $targetType, IntegerType|RealType $parameterType): IntegerType|RealType {
			if ((string)$parameterType->numberRange === '0') {
				return $targetType;
			}
			$subsetType = $this->getMinusSubsetType(
				$targetType, $parameterType
			);
			if ($subsetType !== null) {
				return $subsetType;
			}
			$interval = $this->getMinusRange($targetType, $parameterType);
			return $this->typeRegistry->realFull($interval);
		};
	}

	protected function getExecutor(): callable {
		return fn(IntegerValue|RealValue $target, IntegerValue|RealValue $parameter): RealValue =>
			$this->valueRegistry->real(
				$target->literalValue - $parameter->literalValue
			);
	}
}
