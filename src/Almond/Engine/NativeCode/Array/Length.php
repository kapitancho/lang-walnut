<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, NullType, NullValue> */
final readonly class Length extends ArrayNativeMethod {

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, NullType $parameterType): IntegerType {
			if ($targetType instanceof TupleType) {
				$l = count($targetType->types);
				return $this->typeRegistry->integer(
					$l,
					$targetType->restType instanceof NothingType ? $l : PlusInfinity::value
				);
			}
			return $this->typeRegistry->integer(
				$targetType->range->minLength,
				$targetType->range->maxLength
			);
		};
	}

	protected function getExecutor(): callable {
		return fn(TupleValue $target, NullValue $parameter): IntegerValue =>
			$this->valueRegistry->integer(count($target->values));
	}

}
