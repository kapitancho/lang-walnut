<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ArrayType|TupleType, NullType, TupleValue, NullValue> */
final readonly class CountValues extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		if (!parent::isTargetTypeValid($targetType, $validator, $origin)) {
			return false;
		}
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		/** @var ArrayType $type */
		$itemType = $type->itemType;
		return $itemType->isSubtypeOf($this->typeRegistry->string()) ||
			$itemType->isSubtypeOf($this->typeRegistry->integer());
	}

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, NullType $parameterType): Type {
			$targetType = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
			$itemType = $targetType->itemType;
			if ($itemType->isSubtypeOf($this->typeRegistry->string())) {
				$keyType = $itemType;
			} else {
				$baseItemType = $this->toBaseType($itemType);
				$keyType = $this->typeRegistry->string(
					1,
					($max = $baseItemType->numberRange->max) instanceof NumberIntervalEndpoint &&
					($min = $baseItemType->numberRange->min) instanceof NumberIntervalEndpoint ?
						max(1,
							(int)ceil(log10(abs((int)(string)$max->value))),
							(int)ceil(log10(abs((int)(string)$min->value))) +
							($min->value < 0 ? 1 : 0)
						) : 1000
				);
			}
			return $this->typeRegistry->map(
				$this->typeRegistry->integer(1, max(1, $targetType->range->maxLength)),
				min(1, $targetType->range->minLength),
				$targetType->range->maxLength,
				$keyType
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, NullValue $parameter): Value {
			$rawValues = [];
			foreach ($target->values as $value) {
				if ($value instanceof StringValue) {
					$rawValues[] = $value->literalValue;
				} elseif ($value instanceof IntegerValue) {
					$rawValues[] = (string)$value->literalValue;
				} else {
					// @codeCoverageIgnoreStart
					throw new ExecutionException("Invalid target value");
					// @codeCoverageIgnoreEnd
				}
			}
			$rawValues = array_count_values($rawValues);
			return $this->valueRegistry->record(array_map(
				fn($value) => $this->valueRegistry->integer($value),
				$rawValues
			));
		};
	}

}
