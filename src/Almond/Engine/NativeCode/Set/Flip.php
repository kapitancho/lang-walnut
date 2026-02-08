<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\SetNativeMethod;

/** @extends SetNativeMethod<StringType, NullType, NullValue> */
final readonly class Flip extends SetNativeMethod {

	protected function isTargetItemTypeValid(Type $targetItemType, mixed $origin): bool {
		return $targetItemType->isSubtypeOf($this->typeRegistry->string());
	}

	protected function getValidator(): callable {
		return fn(SetType $targetType, NullType $parameterType): MapType =>
			$this->typeRegistry->map(
				$this->typeRegistry->integer(
					0, $targetType->range->maxLength
				),
				$targetType->range->minLength,
				$targetType->range->maxLength,
				$targetType->itemType
			);
	}

	protected function getExecutor(): callable {
		return function(SetValue $target, NullValue $parameter): Value {
			$rawValues = [];
			foreach ($target->values as $value) {
				/** @var StringValue $value */
				$rawValues[] = $value->literalValue;
			}
			$rawValues = array_flip($rawValues);
			return $this->valueRegistry->record(array_map(
				fn($value) => $this->valueRegistry->integer($value),
				$rawValues
			));
		};
	}

}
