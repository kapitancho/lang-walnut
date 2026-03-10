<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Value;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ValueType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ValueValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

/** @extends NativeMethod<ValueType, FunctionType, ValueValue, FunctionValue> */
final readonly class Filter extends NativeMethod {
	use BaseType;

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
		$returnType = $this->toBaseType($parameterType->returnType);
		if (!$returnType->isSubtypeOf($this->typeRegistry->boolean)) {
			return sprintf(
				"The filter function must return a Boolean, but returns %s",
				$parameterType->returnType
			);
		}
		return null;
	}

	protected function getValidator(): callable {
		return function(ValueType $targetType, FunctionType $parameterType, mixed $origin): Type|ValidationFailure {
			return $this->typeRegistry->result(
				$targetType,
				$this->typeRegistry->null,
			);
		};
	}

	protected function getExecutor(): callable {
		return function(ValueValue $target, FunctionValue $parameter): Value {
			$r = $parameter->execute($target->value);
			if ($r instanceof ErrorValue) {
				return $r;
			}
			if ($this->valueRegistry->true->equals($r)) {
				return $target;
			}
			return $this->valueRegistry->error($this->valueRegistry->null);
		};
	}

}
