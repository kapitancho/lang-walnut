<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MutableNativeMethod;

/** @extends MutableNativeMethod<ArrayType, NullType, NullValue> */
final readonly class SHIFT extends MutableNativeMethod {

	protected function isTargetValueTypeValid(Type $targetValueType, mixed $origin): bool {
		return $targetValueType instanceof ArrayType && (int)(string)$targetValueType->range->minLength === 0;
	}

	protected function getValidator(): callable {
		return function(MutableType $targetType, NullType $parameterType): ResultType {
			$valueType = $this->toBaseType($targetType->valueType);
			/** @var ArrayType $valueType */
			return $this->typeRegistry->result(
				$valueType->itemType,
				$this->typeRegistry->core->itemNotFound
			);
		};
	}

	protected function getExecutor(): callable {
		return function(MutableValue $target, NullValue $parameter): Value {
			$values = $target->value->values;
			if (count($values) > 0) {
				$value = array_shift($values);
				$target->value = $this->valueRegistry->tuple($values);
				return $value;
			}
			return $this->valueRegistry->error(
				$this->valueRegistry->core->itemNotFound
			);
		};
	}

}
