<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonAlias;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\SetNativeMethod;

/** @extends SetNativeMethod<AnyType, NullType, NullValue> */
readonly class SetValues extends SetNativeMethod {

	protected function getValidator(): callable {
		return fn(SetType $targetType, NullType $parameterType, mixed $origin): ArrayType =>
			$this->typeRegistry->array(
				$targetType->itemType,
				$targetType->range->minLength,
				$targetType->range->maxLength
			);
	}

	protected function getExecutor(): callable {
		return fn(SetValue $target, NullValue $parameter): TupleValue =>
			$this->valueRegistry->tuple($target->values);
	}

}