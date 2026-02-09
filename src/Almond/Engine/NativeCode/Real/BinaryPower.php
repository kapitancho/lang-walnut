<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Real;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType|RealType, IntegerType|RealType, IntegerValue|RealValue, IntegerValue|RealValue> */
final readonly class BinaryPower extends NativeMethod {

	protected function getValidator(): callable {
		return function(IntegerType|RealType $targetType, IntegerType|RealType $parameterType): Type {
			if ((string)$parameterType->numberRange === '1') {
				return $targetType;
			}
			if ((string)$parameterType->numberRange === '0') {
				return $this->typeRegistry->integerSubset([new Number(1)]);
			}

			if ($parameterType instanceof IntegerSubsetType && array_all(
					$parameterType->subsetValues, fn(Number $value)
				=> (int)(string)$value % 2 === 0
			)) {
				return $this->typeRegistry->integer(0);
			}

			$containsZero = $targetType->contains(0);
			return $containsZero ?
				$this->typeRegistry->real() :
				$this->typeRegistry->nonZeroReal();
		};
	}

	protected function getExecutor(): callable {
		return fn(IntegerValue|RealValue $target, IntegerValue|RealValue $parameter): RealValue =>
			$this->valueRegistry->real(
				str_contains($parameter->literalValue, '.') ?
					((float)(string)$target->literalValue) ** ((float)(string)$parameter->literalValue) :
					$target->literalValue->pow($parameter->literalValue)
			);
	}
}
