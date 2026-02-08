<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonAlias;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ArrayType|TupleType, NullType, TupleValue, NullValue> */
readonly class ArrayReverse extends NativeMethod {

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, NullType $parameterType, mixed $origin): ArrayType|TupleType {
			if ($targetType instanceof TupleType) {
				if ($targetType->restType instanceof NothingType) {
					return $this->typeRegistry->tuple(
						array_reverse($targetType->types),
						null
					);
				}
				return $targetType->asArrayType();
			}
			return $targetType;
		};
	}

	protected function getExecutor(): callable {
		return fn(TupleValue $target, NullValue $parameter): TupleValue =>
			$this->valueRegistry->tuple(array_reverse($target->values));
	}

}
