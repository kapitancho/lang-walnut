<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<BytesType, NullType, BytesValue, NullValue> */
final readonly class Length extends NativeMethod {

	protected function getValidator(): callable {
		return fn(BytesType $targetType, NullType $parameterType): IntegerType =>
			$this->typeRegistry->integer(
				$targetType->range->minLength,
				$targetType->range->maxLength,
			);
	}

	protected function getExecutor(): callable {
		return fn(BytesValue $target, NullValue $parameter): IntegerValue =>
			$this->valueRegistry->integer(strlen($target->literalValue));
	}

}
