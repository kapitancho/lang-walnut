<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Real;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType|RealType, NullType, IntegerValue|RealValue, NullValue> */
final readonly class Sqrt extends NativeMethod {

	protected function getValidator(): callable {
		return function(IntegerType|RealType $targetType, NullType $parameterType): Type {
			$real = $this->typeRegistry->real(0);
			$minValue = $targetType->numberRange->min;
			return $minValue === MinusInfinity::value || $minValue->value < 0 ?
				$this->typeRegistry->result(
					$real,
					$this->typeRegistry->core->notANumber
				) :
				$real;
		};
	}

	protected function getExecutor(): callable {
		return function(IntegerValue|RealValue $target, NullValue $parameter): Value {
			$val = (string)$target->literalValue;
			return $val >= 0 ?
				$this->valueRegistry->real($target->literalValue->sqrt()) :
				$this->valueRegistry->error(
					$this->valueRegistry->core->notANumber
				);
		};
	}
}
