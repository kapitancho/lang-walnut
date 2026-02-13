<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<BytesType, NullType, BytesValue, NullValue> */
final readonly class AsString extends NativeMethod {

	protected function getValidator(): callable {
		return fn(BytesType $targetType, NullType $parameterType, mixed $origin) =>
			$this->typeRegistry->result(
				$this->typeRegistry->string(
					$targetType->range->minLength->div(4)->ceil(),
					$targetType->range->maxLength,
				),
				$this->typeRegistry->core->invalidString
			);
	}

	protected function getExecutor(): callable {
		return function(BytesValue $target, NullValue $parameter): Value {
			$str = $target->literalValue;
			$isValidString = mb_check_encoding($str, 'UTF-8');
			return $isValidString ?
				$this->valueRegistry->string($str) :
				$this->valueRegistry->error($this->valueRegistry->core->invalidString);
		};
	}
}