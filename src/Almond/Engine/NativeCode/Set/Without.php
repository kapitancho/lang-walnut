<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\SetNativeMethod;

/** @extends SetNativeMethod<Type, Value> */
final readonly class Without extends SetNativeMethod {

	protected function getValidator(): callable {
		return fn(SetType $targetType, Type $parameterType): SetType =>
			$this->typeRegistry->set(
				$targetType->itemType,
				max(0, $targetType->range->minLength - 1),
				$targetType->range->maxLength
			);
	}

	protected function getExecutor(): callable {
		return function(SetValue $target, Value $parameter): SetValue {
			$values = $target->valueSet;
			$key = (string)$parameter;
			if (array_key_exists($key, $values)) {
				unset($values[$key]);
			}
			return $this->valueRegistry->set(array_values($values));
		};
	}

}
