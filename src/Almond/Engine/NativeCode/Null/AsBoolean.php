<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Null;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<NullType, NullType, NullValue, NullValue> */
final readonly class AsBoolean extends NativeMethod {

	protected function getValidator(): callable {
		return fn(NullType $targetType, NullType $parameterType): FalseType =>
			$this->typeRegistry->false;
	}

	protected function getExecutor(): callable {
		return fn(NullValue $target, NullValue $parameter): BooleanValue =>
			$this->valueRegistry->false;
	}

}
