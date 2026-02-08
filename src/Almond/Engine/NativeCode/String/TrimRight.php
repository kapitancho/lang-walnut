<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, StringType|NullType, StringValue, StringValue|NullValue */
final readonly class TrimRight extends NativeMethod {

	protected function getValidator(): callable {
		return fn(StringType $targetType, StringType|NullType $parameterType): StringType =>
		$this->typeRegistry->string(0, $targetType->range->maxLength);
	}

	protected function getExecutor(): callable {
		return fn(StringValue $target, StringValue|NullValue $parameter): StringValue =>
		$this->valueRegistry->string(
			$parameter instanceof StringValue ?
				rtrim($target->literalValue, $parameter->literalValue) :
				rtrim($target->literalValue)
		);
	}

}