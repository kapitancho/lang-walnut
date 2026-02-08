<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ArrayType|TupleType, NullType, TupleValue, NullValue> */
final readonly class Last extends NativeMethod {

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, NullType $parameterType): Type {
			if ($targetType instanceof TupleType) {
				if (count($targetType->types) > 0) {
					$lastType = $targetType->types[count($targetType->types) - 1];
					return $this->toBaseType($targetType->restType) instanceof NothingType ?
						$lastType :
						$this->typeRegistry->union([
							$lastType,
							$targetType->restType
						]);
				}
				return $this->typeRegistry->result(
					$targetType->restType,
					$this->typeRegistry->core->itemNotFound
				);
			}
			return $targetType->range->minLength > 0 ?
				$targetType->itemType : $this->typeRegistry->result(
					$targetType->itemType,
					$this->typeRegistry->core->itemNotFound
				);
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, NullValue $parameter): Value {
			$values = $target->values;
			$count = count($values);
			if ($count > 0) {
				return $values[$count - 1];
			}
			return $this->valueRegistry->error(
				$this->valueRegistry->core->itemNotFound
			);
		};
	}

}
