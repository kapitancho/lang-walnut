<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Optional;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EmptyValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<OptionalType, NullType, BooleanValue, NullValue> */
readonly class AsBoolean extends NativeMethod {

	protected function getValidator(): callable {
		return function(OptionalType $targetType, NullType $parameterType, mixed $origin): BooleanType|FalseType|ValidationFailure {
			$innerType = $this->toBaseType($targetType->valueType);
			$result = $this->methodContext->validateMethod(
				$innerType,
				new MethodName('asBoolean'),
				$parameterType,
				$origin
			);
			if ($result instanceof ValidationSuccess) {
				return $result->type instanceof FalseType ?
					$result->type :
					$this->typeRegistry->boolean;
			}
			return $result;
		};
	}

	protected function getExecutor(): callable {
		return fn(EmptyValue $target, NullValue $parameter): BooleanValue =>
			$this->valueRegistry->false;
	}

}
