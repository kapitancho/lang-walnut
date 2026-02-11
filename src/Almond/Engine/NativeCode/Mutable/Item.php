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
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<MutableType, Type, MutableValue, Value> */
final readonly class Item extends NativeMethod {

	protected function getValidator(): callable {
		return function(MutableType $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
			$result = $this->methodContext->validateMethod(
				$targetType->valueType,
				new MethodName('item'),
				$parameterType,
				$origin
			);
			if ($result instanceof ValidationFailure && ($result->errors[0]?->type === ValidationErrorType::undefinedMethod)) {
				return $this->validationFactory->error(
					ValidationErrorType::invalidTargetType,
					sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
					origin: $origin
				);
			}
			return $result;
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
