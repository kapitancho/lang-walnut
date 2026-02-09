<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<IntegerType, NullType, IntegerValue, NullValue> */
final readonly class AsString extends NativeMethod {

	protected function getValidator(): callable {
		return function(IntegerType $targetType, NullType $parameterType): StringType {
			$min = $targetType->numberRange->min;
			$max = $targetType->numberRange->max;
			$minLength = match(true) {
				$max !== PlusInfinity::value && $max->value <= 0 =>
					strlen((string)$max->value->sub($max->inclusive ? 0 : 1)),
				$min !== MinusInfinity::value && $min->value >= 0 =>
					strlen((string)$min->value->add($min->inclusive ? 0 : 1)),
				default => 1,
			};
			$maxLength = match(true) {
				$max === PlusInfinity::value, $min === MinusInfinity::value => PlusInfinity::value,
				default => max(
					1,
					(int)ceil(log10(1 + abs((int)(string)$max->value))),
					1 + (int)ceil( log10(1 + abs((int)(string)$min->value))),
				)
			};
			return $this->typeRegistry->string($minLength, $maxLength);
		};
	}

	protected function getExecutor(): callable {
		return fn(IntegerValue $target, NullValue $parameter): StringValue =>
			$this->valueRegistry->string((string)$target->literalValue);
	}
}
