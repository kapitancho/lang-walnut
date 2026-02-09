<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NumericRangeHelper;

/** @extends NativeMethod<IntegerType, IntegerType|RealType, IntegerValue, IntegerValue|RealValue> */
final readonly class BinaryMinus extends NativeMethod {
	use NumericRangeHelper;

	protected function getValidator(): callable {
		return function(IntegerType $targetType, IntegerType|RealType $parameterType): IntegerType|RealType {
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
			return $parameterType instanceof IntegerType ?
				$this->typeRegistry->integerFull($interval) :
				$this->typeRegistry->realFull($interval);
		};
	}

	protected function getExecutor(): callable {
		return function(IntegerValue $target, IntegerValue|RealValue $parameter): Value {
			if ($parameter instanceof IntegerValue) {
				return $this->valueRegistry->integer(
					$target->literalValue - $parameter->literalValue
				);
			}
			return $this->valueRegistry->real(
				$target->literalValue - $parameter->literalValue
			);
		};
	}
}
