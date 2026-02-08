<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MutableNativeMethod;

/** @extends MutableNativeMethod<AnyType, NullType, NullValue> */
final readonly class AsReal extends MutableNativeMethod {

	protected function getValidator(): callable {
		return function(MutableType $targetType, NullType $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
			return $this->methodContext->validateMethod(
				$targetType->valueType,
				new MethodName('asReal'),
				$parameterType,
				$origin
			);
		};
	}

	protected function isTargetValueTypeValid(Type $targetValueType, mixed $origin): bool {
		$result = $this->methodContext->validateMethod(
			$targetValueType,
			new MethodName('asReal'),
			$this->typeRegistry->null,
			$origin
		);
		return !($result instanceof ValidationFailure && ($result->errors[0]?->type === ValidationErrorType::undefinedMethod));
	}

	protected function getExecutor(): callable {
		return fn(MutableValue $target, NullValue $parameter): Value =>
			$this->methodContext->executeMethod(
				$target->value,
				new MethodName('asReal'),
				$parameter
			);
	}
}
