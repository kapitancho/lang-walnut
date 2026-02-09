<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Real;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType|RealType, NullType, IntegerValue|RealValue, NullValue> */
final readonly class Sign extends NativeMethod {

	protected function getValidator(): callable {
		return function(IntegerType|RealType $targetType, NullType $parameterType): IntegerSubsetType {
			$min = $targetType->numberRange->min;
			$max = $targetType->numberRange->max;

			$values = [];
			if ($min === MinusInfinity::value || (string)$min->value < 0) {
				$values[] = new Number(-1);
			}
			if (($min === MinusInfinity::value || (string)$min->value <= 0) && ($max === PlusInfinity::value || (string)$max->value >= 0)) {
				$values[] = new Number(0);
			}
			if ($max === PlusInfinity::value || (string)$max->value > 0) {
				$values[] = new Number(1);
			}
			return $this->typeRegistry->integerSubset($values);
		};
	}

	protected function getExecutor(): callable {
		return function(IntegerValue|RealValue $target, NullValue $parameter): IntegerValue {
			$val = (string)$target->literalValue;
			if ($val > 0) {
				return $this->valueRegistry->integer(1);
			} elseif ($val < 0) {
				return $this->valueRegistry->integer(-1);
			} else {
				return $this->valueRegistry->integer(0);
			}
		};
	}
}
