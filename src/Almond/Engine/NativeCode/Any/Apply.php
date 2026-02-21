<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\TupleAsRecord;

/** @extends NativeMethod<Type, FunctionType, Value, FunctionValue> */
final readonly class Apply extends NativeMethod {
	use TupleAsRecord;

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		$p = $parameterType->parameterType;
		$adjustedTargetType = $this->adjustParameterType(
			$this->typeRegistry,
			$p,
			$targetType,
		);
		return $adjustedTargetType->isSubtypeOf($p) ?
			null :
			sprintf(
				"The target type %s is not a subtype of the function parameter type %s",
				$targetType, $p
			);
	}

	protected function getValidator(): callable {
		return fn(Type $targetType, FunctionType $parameterType, mixed $origin): Type => $parameterType->returnType;
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