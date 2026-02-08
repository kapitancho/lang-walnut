<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MapNativeMethod;

/** @extends MapNativeMethod<Type, NullType, NullValue> */
final readonly class Flip extends MapNativeMethod {

	protected function isTargetItemTypeValid(Type $targetItemType, mixed $origin): bool {
		return $targetItemType->isSubtypeOf($this->typeRegistry->string());
	}

	protected function getValidator(): callable {
		return fn(MapType $targetType, NullType $parameterType): MapType =>
			$this->typeRegistry->map(
				$targetType->keyType,
				min(1, $targetType->range->minLength),
				$targetType->range->maxLength,
				$targetType->itemType
			);
	}

	protected function getExecutor(): callable {
		return function(RecordValue $target, NullValue $parameter): RecordValue {
			$rawValues = [];
			foreach ($target->values as $key => $value) {
				if ($value instanceof StringValue) {
					$rawValues[$key] = $value->literalValue;
				} else {
					// @codeCoverageIgnoreStart
					throw new ExecutionException("Invalid target value");
					// @codeCoverageIgnoreEnd
				}
			}
			$rawValues = array_flip($rawValues);
			return $this->valueRegistry->record(array_map(
				fn($value) => $this->valueRegistry->string($value),
				$rawValues
			));
		};
	}

}
