<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<Type, Type, Value, Value> */
final readonly class Printed extends NativeMethod {

	protected function getValidator(): callable {
		return fn(Type $targetType, Type $parameterType): StringType =>
			$this->typeRegistry->string();
	}

	protected function getExecutor(): callable {
		return fn(Value $target, Value $parameter): StringValue =>
			$this->valueRegistry->string((string)$target);
	}

}
