<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MutableNativeMethod;

/** @extends MutableNativeMethod<ArrayType, Type, Value> */
final readonly class PUSH extends MutableNativeMethod {

	protected function validateTargetValueType(Type $valueType): null|string {
		return $valueType instanceof ArrayType && $valueType->range->maxLength === PlusInfinity::value ?
			null : sprintf(
				"The value type of the target must be an unbounded Array type, got %s",
				$valueType
			);
	}

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		/** @var MutableType $targetType */
		$valueType = $this->toBaseType($targetType->valueType);
		/** @var ArrayType $valueType */
		return $parameterType->isSubtypeOf($valueType->itemType) ?
			null : sprintf(
				"The parameter type %s is not a subtype of the item type %s",
				$parameterType,
				$valueType->itemType
			);
	}


	protected function getValidator(): callable {
		return fn(MutableType $targetType, Type $parameterType, mixed $origin): MutableType => $targetType;
	}

	protected function getExecutor(): callable {
		return function(MutableValue $target, Value $parameter): MutableValue {
			/** @var TupleValue $targetValue */
			$targetValue = $target->value;
			$arr = $targetValue->values;
			$arr[] = $parameter;
			$target->value = $this->valueRegistry->tuple($arr);
			return $target;
		};
	}

}
