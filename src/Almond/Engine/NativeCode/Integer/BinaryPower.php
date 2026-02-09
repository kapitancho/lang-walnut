<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Integer;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType, IntegerType|RealType, IntegerValue, IntegerValue|RealValue> */
final readonly class BinaryPower extends NativeMethod {

	protected function getValidator(): callable {
		return function(IntegerType $targetType, IntegerType|RealType $parameterType): Type {
			if ($parameterType instanceof IntegerSubsetType && array_all(
					$parameterType->subsetValues, fn(Number $value)
				=> (int)(string)$value % 2 === 0
				)) {
				return $this->typeRegistry->integer(0);
			}
			$containsZero = $targetType->contains(0);

			if ($parameterType instanceof IntegerType) {
				return $containsZero ?
					$this->typeRegistry->integer() :
					$this->typeRegistry->nonZeroInteger();
			}
			return $containsZero ?
				$this->typeRegistry->real() :
				$this->typeRegistry->nonZeroReal();
		};
	}

	protected function getExecutor(): callable {
		return function(IntegerValue $target, IntegerValue|RealValue $parameter): Value {
			if ($parameter instanceof IntegerValue) {
				return $this->valueRegistry->integer(
					$target->literalValue ** $parameter->literalValue
				);
			}
			return $this->valueRegistry->real(
				((int)(string)$target->literalValue) ** ((float)(string)$parameter->literalValue)
			);
		};
	}
}
