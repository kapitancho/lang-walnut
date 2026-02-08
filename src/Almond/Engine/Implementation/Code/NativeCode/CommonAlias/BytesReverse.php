<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonAlias;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<BytesType, NullType, BytesValue, NullValue> */
readonly class BytesReverse extends NativeMethod {

	protected function getValidator(): callable {
		return fn(BytesType $targetType, NullType $parameterType): BytesType =>
			$targetType;
	}

	protected function getExecutor(): callable {
		return fn(BytesValue $target, NullValue $parameter): BytesValue =>
			$this->valueRegistry->bytes(strrev($target->literalValue));
	}

}
