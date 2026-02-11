<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, NullType, NullValue> */
final readonly class Unique extends ArrayNativeMethod {

	protected function isTargetItemTypeValid(Type $targetItemType, mixed $origin): bool {
		return $targetItemType->isSubtypeOf($this->typeRegistry->string()) ||
			$targetItemType->isSubtypeOf(
				$this->typeRegistry->union([$this->typeRegistry->integer(), $this->typeRegistry->real()])
			);
	}

	protected function getValidator(): callable {
		return fn(ArrayType $targetType, NullType $parameterType): ArrayType =>
			$this->typeRegistry->array(
				$targetType->itemType,
				min(1, $targetType->range->minLength),
				$targetType->range->maxLength
			);
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, NullValue $parameter): TupleValue {
			$values = $target->values;
			$rawValues = [];
			$hasStrings = false;
			foreach ($values as $value) {
				if ($value instanceof StringValue) {
					$hasStrings = true;
				}
				$rawValues[] = (string)$value->literalValue;
			}
			if ($hasStrings) {
				$rawValues = array_unique($rawValues);
				return $this->valueRegistry->tuple(array_map(
					fn(string $value) => $this->valueRegistry->string($value),
					$rawValues
				));
			}
			$rawValues = array_unique($rawValues, SORT_NUMERIC);
			return $this->valueRegistry->tuple(array_map(
				fn(string $value) => str_contains($value, '.') ?
					$this->valueRegistry->real((float)$value) :
					$this->valueRegistry->integer((int)$value),
				$rawValues
			));
		};
	}

}
