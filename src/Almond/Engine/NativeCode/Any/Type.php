<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type as TypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<Type, NullType, Value, NullValue> */
final readonly class Type extends NativeMethod {

	protected function getValidator(): callable {
		return fn(TypeInterface $targetType, NullType $parameterType): TypeType =>
			$this->typeRegistry->type($targetType);
	}

	protected function getExecutor(): callable {
		return fn(Value $target, NullValue $parameter): Value =>
			$this->valueRegistry->type($target->type);
	}

}