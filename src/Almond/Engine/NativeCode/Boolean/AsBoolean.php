<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Boolean;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<BooleanType, NullType, BooleanValue, NullValue> */
final readonly class AsBoolean extends NativeMethod {

	protected function getValidator(): callable {
		return fn(BooleanType $targetType, NullType $parameterType): BooleanType =>
			$this->typeRegistry->boolean;
	}

	protected function getExecutor(): callable {
		return fn(BooleanValue $target, NullValue $parameter): BooleanValue =>
			$target;
	}

}
