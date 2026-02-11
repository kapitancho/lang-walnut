<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonAlias;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ResultType, Type, ErrorValue, Value> */
abstract readonly class ResultProxy extends NativeMethod {

	abstract protected function methodName(): MethodName;

	protected function getValidator(): callable {
		return function(ResultType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$innerType = $this->toBaseType($targetType->returnType);
			$result = $this->methodContext->validateMethod(
				$innerType,
				$this->methodName(),
				$parameterType,
				$origin
			);
			if ($result instanceof ValidationSuccess) {
				return $this->typeRegistry->result($result->type, $targetType->errorType);
			}
			return $result;
		};
	}

	protected function getExecutor(): callable {
		return fn(ErrorValue $target, Value $parameter): ErrorValue => $target;
	}

}
