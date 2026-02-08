<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MapNativeMethod;

/** @extends MapNativeMethod<Type, NullType, NullValue> */
final readonly class KeysSet extends MapNativeMethod {

	protected function getValidator(): callable {
		return fn(MapType $targetType, NullType $parameterType): Type =>
			$this->typeRegistry->set(
				$targetType->keyType,
				$targetType->range->minLength,
				$targetType->range->maxLength
			);
	}

	protected function getExecutor(): callable {
		return fn(RecordValue $target, NullValue $parameter): Value =>
			$this->valueRegistry->set(
				array_map(
					fn($key) => $this->valueRegistry->string($key),
					array_keys($target->values)
				)
			);
	}

}
