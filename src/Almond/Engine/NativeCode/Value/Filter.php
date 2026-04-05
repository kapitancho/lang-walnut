<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Value;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ValueType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ValueValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ValueType, FunctionType, ValueValue, FunctionValue> */
final readonly class Filter extends NativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		/** @var ValueType $targetType */
		/** @var FunctionType $parameterType */
		if (!$targetType->valueType->isSubtypeOf($parameterType->parameterType)) {
			return sprintf(
				"The value type %s is not a subtype of the of the callback function parameter type %s",
				$targetType->valueType,
				$parameterType->parameterType
			);
		}
		$expectedReturnType = $this->typeRegistry->result($this->typeRegistry->boolean, $this->typeRegistry->any);
		if (!$parameterType->returnType->isSubtypeOf($expectedReturnType)) {
			return sprintf(
				"The return type of the callback function must be a subtype of %s, but got %s",
				$expectedReturnType,
				$parameterType->returnType
			);
		}
		return null;
	}

	protected function getValidator(): callable {
		return function(ValueType $targetType, FunctionType $parameterType, mixed $origin): Type|ValidationFailure {
			$returnType = $this->typeRegistry->optional($targetType);

			$pType = $this->toBaseType($parameterType->returnType);
			return $pType instanceof ResultType ? $this->typeRegistry->result(
				$returnType,
				$pType->errorType
			) : $returnType;
		};
	}

	protected function getExecutor(): callable {
		return function(ValueValue $target, FunctionValue $parameter): Value {
			$r = $parameter->execute($target->value);
			if ($r instanceof ErrorValue) {
				return $r;
			}
			return $this->valueRegistry->true->equals($r) ?
				$target : $this->valueRegistry->empty;
		};
	}

}
