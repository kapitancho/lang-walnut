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
final readonly class UNSHIFT extends MutableNativeMethod {

	protected function isTargetValueTypeValid(Type $targetValueType, mixed $origin): bool {
		return $targetValueType instanceof ArrayType && $targetValueType->range->maxLength === PlusInfinity::value;
	}

	protected function getValidator(): callable {
		return function(MutableType $targetType, Type $parameterType, mixed $origin): MutableType|ValidationFailure {
			$valueType = $this->toBaseType($targetType->valueType);
			/** @var ArrayType $valueType */
			if ($parameterType->isSubtypeOf($valueType->itemType)) {
				return $targetType;
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				$origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(MutableValue $target, Value $parameter): MutableValue {
			/** @var TupleValue $targetValue */
			$targetValue = $target->value;
			$arr = $targetValue->values;
			array_unshift($arr, $parameter);
			$target->value = $this->valueRegistry->tuple($arr);
			return $target;
		};
	}

}
