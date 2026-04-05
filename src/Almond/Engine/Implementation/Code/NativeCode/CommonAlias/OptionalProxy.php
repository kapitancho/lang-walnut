<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonAlias;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EmptyValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<OptionalType, Type, EmptyValue, Value> */
abstract readonly class OptionalProxy extends NativeMethod {

	abstract protected function methodName(): MethodName;

	protected function getValidator(): callable {
		return function(OptionalType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$innerType = $this->toBaseType($targetType->valueType);
			$result = $this->methodContext->validateMethod(
				$innerType,
				$this->methodName(),
				$parameterType,
				$origin
			);
			if ($result instanceof ValidationSuccess) {
				return $this->typeRegistry->optional($result->type);
			}
			return $result;
		};
	}

	protected function getExecutor(): callable {
		return fn(EmptyValue $target, Value $parameter): EmptyValue => $target;
	}

}
