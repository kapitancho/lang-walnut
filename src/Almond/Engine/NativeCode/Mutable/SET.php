<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MutableNativeMethod;

/** @extends MutableNativeMethod<Type, Type, Value> */
final readonly class SET extends MutableNativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		/** @var MutableType $targetType */
		return $parameterType->isSubtypeOf($targetType->valueType) ?
			null :
			sprintf(
				"The parameter type %s is not a subtype of the value type %s",
				$parameterType,
				$targetType->valueType
			);
	}

	protected function getValidator(): callable {
		return fn(MutableType $targetType, Type $parameterType, mixed $origin): MutableType => $targetType;
	}

	protected function getExecutor(): callable {
		return function(MutableValue $target, Value $parameter): MutableValue {
			$target->value = $parameter;
			return $target;
		};
	}

}
