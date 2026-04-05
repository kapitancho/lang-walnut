<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EmptyValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\TupleAsRecord;

/** @extends NativeMethod<Type, FunctionType, Value, FunctionValue> */
final readonly class IfEmpty extends NativeMethod {
	use TupleAsRecord;

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		/** @var FunctionType $parameterType */
		return $this->typeRegistry->null->isSubtypeOf($parameterType->parameterType) ? null :
			sprintf(
				"The parameter type %s of the callback function is not a subtype of %s",
				$parameterType->parameterType,
				$this->typeRegistry->null
			);
	}

	protected function getValidator(): callable {
		return function(Type $targetType, FunctionType $parameterType, mixed $origin): Type|ValidationFailure {
			$targetReturnType = match(true) {
				$targetType instanceof OptionalType => $targetType->valueType,
				default => $targetType,
			};
			$returnType = $parameterType->returnType;
			$callbackReturnType = match(true) {
				$targetType instanceof AnyType, $targetType instanceof OptionalType => $returnType,
				default => $this->typeRegistry->nothing
			};
			return $this->typeRegistry->union([$targetReturnType, $callbackReturnType]);
		};
	}

	protected function getExecutor(): callable {
		return function(Value $target, FunctionValue $parameter): Value {
			return $target instanceof EmptyValue ?
				$parameter->execute($this->valueRegistry->null) :
				$target;
		};
	}
}