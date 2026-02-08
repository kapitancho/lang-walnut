<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, NullType, NullValue> */
final readonly class Shuffle extends ArrayNativeMethod {

	protected function getValidator(): callable {
		return fn(ArrayType $targetType, NullType $parameterType): ArrayType =>
			$targetType;
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, NullValue $parameter): TupleValue {
			$values = $target->values;
			shuffle($values);
			return $this->valueRegistry->tuple($values);
		};
	}

}
