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
final readonly class Map extends NativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		/** @var ValueType $targetType */
		/** @var FunctionType $parameterType */
		if (!$targetType->valueType->isSubtypeOf($parameterType->parameterType)) {
			return sprintf(
				"The value type %s is not a subtype of the callback function parameter type %s",
				$targetType->valueType,
				$parameterType->parameterType
			);
		}
		return null;
	}

	protected function getValidator(): callable {
		return function(ValueType $targetType, FunctionType $parameterType, mixed $origin): Type|ValidationFailure {
			$r = $parameterType->returnType;
			$errorType = $r instanceof ResultType ? $r->errorType : null;
			$returnType = $r instanceof ResultType ? $r->returnType : $r;
			$t = $this->typeRegistry->value($returnType);
			return $errorType ? $this->typeRegistry->result($t, $errorType) : $t;
		};
	}

	protected function getExecutor(): callable {
		return function(ValueValue $target, FunctionValue $parameter): Value {
			$r = $parameter->execute($target->value);
			if ($r instanceof ErrorValue) {
				return $r;
			}
			return $this->valueRegistry->value($r);
		};
	}

}
