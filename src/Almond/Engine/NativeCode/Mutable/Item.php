<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MapNativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends MapNativeMethod<Type, Type, Value> */
final readonly class Item extends MapNativeMethod {

	protected function getValidator(): callable {
		return function(MutableType $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
			return $this->methodContext->validateMethod(
				$targetType->valueType,
				new MethodName('item'),
				$parameterType,
				$origin
			);
		};
	}

	protected function getExecutor(): callable {
		return fn(MutableValue $target, Value $parameter): Value =>
			$this->methodContext->executeMethod(
				$target->value,
				new MethodName('item'),
				$parameter
			);
	}
}
