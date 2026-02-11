<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\TupleAsRecord;

/** @extends NativeMethod<Type, FunctionType, Value, FunctionValue> */
final readonly class IfError extends NativeMethod {
	use TupleAsRecord;

	protected function getValidator(): callable {
		return function(Type $targetType, FunctionType $parameterType, mixed $origin): Type|ValidationFailure {
			$targetReturnType = match(true) {
				$targetType instanceof ResultType => $targetType->returnType,
				default => $this->typeRegistry->any,
			};
			$errorType = match(true) {
				$targetType instanceof ResultType => $targetType->errorType,
				$targetType instanceof AnyType => $this->typeRegistry->any,
				default => $this->typeRegistry->nothing,
			};
			// Callback should accept the error type
			if ($errorType->isSubtypeOf($parameterType->parameterType)) {
				// Return type is union of success type and callback return type
				$returnType = $parameterType->returnType;
				return $this->typeRegistry->union([$targetReturnType, $returnType]);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidTargetType,
				sprintf(
					"The parameter type %s of the callback function is not a subtype of %s",
					$errorType,
					$parameterType->parameterType
				),
				$origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(Value $target, FunctionValue $parameter): Value {
			return $target instanceof ErrorValue ?
				$parameter->execute($target->errorValue) :
				$target;
		};
	}
}