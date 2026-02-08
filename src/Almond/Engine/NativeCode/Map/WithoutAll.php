<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MapNativeMethod;

/** @extends MapNativeMethod<Type, Type, Value> */
final readonly class WithoutAll extends MapNativeMethod {

	protected function getValidator(): callable {
		return fn(MapType $targetType, Type $parameterType): MapType =>
			$this->typeRegistry->map(
				$targetType->itemType,
				0,
				$targetType->range->maxLength,
				$targetType->keyType
			);
	}

	protected function getExecutor(): callable {
		return fn(RecordValue $target, Value $parameter): RecordValue =>
			$this->valueRegistry->record(
				array_filter($target->values, static fn($value) => !$value->equals($parameter))
			);
	}

}
