<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<BytesType, BytesType, BytesValue, BytesValue> */
final readonly class BinaryLessThan extends NativeMethod {

	protected function getValidator(): callable {
		return fn(BytesType $targetType, BytesType $parameterType): BooleanType =>
			$this->typeRegistry->boolean;
	}

	protected function getExecutor(): callable {
		return fn(BytesValue $target, BytesValue $parameter): BooleanValue =>
			$this->valueRegistry->boolean($target->literalValue < $parameter->literalValue);
	}

}
