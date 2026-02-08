<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\SetNativeMethod;

/** @extends SetNativeMethod<NullType, NullValue> */
final readonly class AsBoolean extends SetNativeMethod {

	protected function getValidator(): callable {
		return fn(SetType $targetType, NullType $parameterType): BooleanType =>
			$this->typeRegistry->boolean;
	}

	protected function getExecutor(): callable {
		return fn(SetValue $target, NullValue $parameter): BooleanValue =>
			$this->valueRegistry->boolean(count($target->values));
	}

}
