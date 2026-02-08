<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, NullType, StringValue, NullValue */
final readonly class Length extends NativeMethod {

	protected function getValidator(): callable {
		return fn(StringType $targetType, NullType $parameterType, Expression|null $origin): IntegerType =>
			$this->typeRegistry->integer(
				$targetType->range->minLength,
				$targetType->range->maxLength,
			);
	}

	protected function getExecutor(): callable {
		return fn(StringValue $target, NullValue $parameter): IntegerValue =>
			$this->valueRegistry->integer(
				mb_strlen($target->literalValue)
			);
	}
}