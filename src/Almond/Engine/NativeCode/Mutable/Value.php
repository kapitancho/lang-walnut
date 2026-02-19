<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value as ValueInterface;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MutableNativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends MutableNativeMethod<Type, NullType, NullValue> */
final readonly class Value extends MutableNativeMethod {

	protected function getValidator(): callable {
		return fn(MutableType|MetaType $targetType, NullType $parameterType): Type =>
			$targetType instanceof MetaType ?
				$this->typeRegistry->any :
				$targetType->valueType;
	}

	protected function getExecutor(): callable {
		return fn(MutableValue $target, NullValue $parameter): ValueInterface =>
			$target->value;
	}
}
