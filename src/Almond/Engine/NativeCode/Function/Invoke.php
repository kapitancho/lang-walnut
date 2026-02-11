<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Function;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type as TypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\TupleAsRecord;

/** @extends NativeMethod<FunctionType, TypeInterface, FunctionValue, Value> */
final readonly class Invoke extends NativeMethod {
	use TupleAsRecord;

	protected function getValidator(): callable {
		return function(FunctionType $targetType, TypeInterface $parameterType, mixed $origin): TypeInterface|ValidationFailure {
			$p = $targetType->parameterType;
			$parameterType = $this->adjustParameterType(
				$this->typeRegistry,
				$p,
				$parameterType,
			);
			if ($parameterType->isSubtypeOf($targetType->parameterType)) {
				return $targetType->returnType;
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("Invalid parameter type: %s, %s expected (target is %s)",
					$parameterType, $targetType->parameterType, $targetType
				),
				$origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(FunctionValue $target, Value $parameter): Value {
			$parameter = $this->adjustParameterValue(
				$this->valueRegistry,
				$target->type->parameterType,
				$parameter,
			);
			return $target->execute($parameter);
		};
	}
}