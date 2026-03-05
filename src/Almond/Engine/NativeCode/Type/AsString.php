<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<TypeType, NullType, TypeValue, NullValue> */
readonly class AsString extends NativeMethod {

	protected function getValidator(): callable {
		return fn(TypeType $targetType, NullType $parameterType): Type =>
			$this->typeRegistry->string(1);
	}

	protected function getExecutor(): callable {
		return fn(TypeValue $target, NullValue $parameter): StringValue =>
			$this->valueRegistry->string((string)$target->typeValue);
	}

}
