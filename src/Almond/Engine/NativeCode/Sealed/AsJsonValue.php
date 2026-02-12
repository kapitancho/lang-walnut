<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Sealed;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SealedValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<SealedType, Type, SealedValue, NullValue> */
final readonly class AsJsonValue extends NativeMethod {

	protected function getValidator(): callable {
		return fn(SealedType $targetType, NullType $parameterType, mixed $origin): ValidationSuccess|ValidationFailure =>
			$this->methodContext->validateMethod(
				$targetType->valueType,
				new MethodName('asJsonValue'),
				$parameterType,
				$origin
			);
	}

	protected function getExecutor(): callable {
		return fn(SealedValue $target, NullValue $parameter): Value =>
			$this->methodContext->executeMethod(
				$target->value,
				new MethodName('asJsonValue'),
				$parameter
			);
	}

}