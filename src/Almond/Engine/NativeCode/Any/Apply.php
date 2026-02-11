<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\TupleAsRecord;

/** @extends NativeMethod<Type, FunctionType, Value, FunctionValue> */
final readonly class Apply extends NativeMethod {
	use TupleAsRecord;

	protected function getValidator(): callable {
		return function(Type $targetType, FunctionType $parameterType, mixed $origin): Type|ValidationFailure {
			$p = $parameterType->parameterType;
			$adjustedTargetType = $this->adjustParameterType(
				$this->typeRegistry,
				$p,
				$targetType,
			);
			if ($adjustedTargetType->isSubtypeOf($p)) {
				return $parameterType->returnType;
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidTargetType,
				sprintf("Invalid target type: %s, %s expected (parameter is %s)",
					$targetType, $p, $parameterType
				),
				$origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(Value $target, FunctionValue $parameter): Value {
			$target = $this->adjustParameterValue(
				$this->valueRegistry,
				$parameter->type->parameterType,
				$target,
			);
			return $parameter->execute($target);
		};
	}
}