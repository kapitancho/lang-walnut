<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<Type, Type, Value, Value> */
final readonly class BinaryOrElse extends NativeMethod {

	protected function getValidator(): callable {
		return function(Type $targetType, Type $parameterType): Type {
			if ($targetType instanceof ResultType) {
				return $this->typeRegistry->union([$targetType->returnType, $parameterType]);
			}
			return $targetType;
		};
	}

	protected function getExecutor(): callable {
		return fn(Value $target, Value $parameter): Value =>
			$target instanceof ErrorValue ? $parameter : $target;
	}

}
