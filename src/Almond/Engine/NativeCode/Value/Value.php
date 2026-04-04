<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Value;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ValueType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ValueValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value as ValueInterface;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ValueType, Type, ValueValue, NullValue> */
final readonly class Value extends NativeMethod {

	protected function getValidator(): callable {
		return fn(ValueType $targetType, NullType $parameterType): Type => $targetType->valueType;
	}

	protected function getExecutor(): callable {
		return fn(ValueValue $target, NullValue $parameter): ValueInterface => $target->value;
	}
}
