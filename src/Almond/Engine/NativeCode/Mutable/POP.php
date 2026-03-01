<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MutableNativeMethod;

final readonly class POP extends MutableNativeMethod {

	protected function validateTargetValueType(Type $valueType): null|string {
		return
			($valueType instanceof TupleType && count($valueType->types) === 0) ||
			($valueType instanceof ArrayType && (int)(string)$valueType->range->minLength === 0) ?
				null : sprintf("The value type of the target set must be a subtype of Array or Tuple with a minimum length of 0, got %s",
				$valueType
			);
	}

	protected function getValidator(): callable {
		return function(MutableType $targetType, NullType $parameterType, mixed $origin): ResultType {
			$vt = $this->toBaseType($targetType->valueType);
			return $this->typeRegistry->result(
				match(true) {
					$vt instanceof TupleType => $vt->restType,
					$vt instanceof ArrayType => $vt->itemType,
					default => $this->typeRegistry->any
				},
				$this->typeRegistry->core->itemNotFound
			);
		};
	}

	protected function getExecutor(): callable {
		return function(MutableValue $target, NullValue $parameter): Value {
			/** @var TupleValue $targetValue */
			$targetValue = $target->value;
			$values = $targetValue->values;
			if (count($values) > 0) {
				$value = array_pop($values);
				$target->value = $this->valueRegistry->tuple($values);
				return $value;
			}
			return $this->valueRegistry->error(
				$this->valueRegistry->core->itemNotFound
			);
		};
	}
}
