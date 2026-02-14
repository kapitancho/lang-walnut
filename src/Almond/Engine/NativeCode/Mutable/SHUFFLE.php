<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MutableNativeMethod;

/** @extends MutableNativeMethod<ArrayType, NullType, NullValue> */
final readonly class SHUFFLE extends MutableNativeMethod {

	protected function isTargetValueTypeValid(Type $targetValueType, mixed $origin): bool {
		return $targetValueType instanceof ArrayType;
	}

	protected function getValidator(): callable {
		return fn(MutableType $targetType, NullType $parameterType): MutableType =>
			$targetType;
	}

	protected function getExecutor(): callable {
		return function(MutableValue $target, NullValue $parameter): MutableValue {
			/** @var TupleValue $targetValue */
			$targetValue = $target->value;
			$values = $targetValue->values;
			shuffle($values);
			$target->value = $this->valueRegistry->tuple($values);
			return $target;
		};
	}

}
