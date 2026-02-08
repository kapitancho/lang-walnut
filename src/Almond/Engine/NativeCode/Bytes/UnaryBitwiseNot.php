<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<BytesType, NullType, BytesValue, NullValue> */
final readonly class UnaryBitwiseNot extends NativeMethod {

	protected function getValidator(): callable {
		return fn(BytesType $targetType, NullType $parameterType): BytesType =>
			$targetType;
	}

	protected function getExecutor(): callable {
		return function(BytesValue $target, NullValue $parameter): BytesValue {
			$bytes = $target->literalValue;
			$result = '';
			for ($i = 0; $i < strlen($bytes); $i++) {
				$result .= chr(~ord($bytes[$i]) & 0xFF);
			}
			return $this->valueRegistry->bytes($result);
		};
	}

}
