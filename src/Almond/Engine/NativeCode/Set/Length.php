<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\SetNativeMethod;

/** @extends SetNativeMethod<NullType, NullValue> */
final readonly class Length extends SetNativeMethod {

	protected function getValidator(): callable {
		return fn(SetType $targetType, NullType $parameterType): IntegerType =>
			$this->typeRegistry->integer(
				$targetType->range->minLength,
				$targetType->range->maxLength
			);
	}

	protected function getExecutor(): callable {
		return fn(SetValue $target, NullValue $parameter): IntegerValue =>
			$this->valueRegistry->integer(count($target->values));
	}

}
