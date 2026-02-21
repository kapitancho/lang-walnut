<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<BytesType, NullType, BytesValue, NullValue> */
final readonly class Ord extends NativeMethod {

	protected function validateTargetType(Type $targetType, mixed $origin): null|string {
		/** @var BytesType $targetType */
		return (string)$targetType->range->minLength === '1' &&
			(string)$targetType->range->maxLength === '1' ?
			null : sprintf("Target type %s is not a subtype of Bytes<1>", $targetType);
	}

	protected function getValidator(): callable {
		return function(BytesType $targetType, NullType $parameterType): IntegerType {
			return $this->typeRegistry->integer(0, 255);
		};
	}

	protected function getExecutor(): callable {
		return fn(BytesValue $target, NullValue $parameter): IntegerValue =>
			$this->valueRegistry->integer(ord($target->literalValue));
	}

}
