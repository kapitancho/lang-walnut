<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\SetNativeMethod;

/** @extends SetNativeMethod<Type, Value> */
final readonly class Insert extends SetNativeMethod {

	protected function getValidator(): callable {
		return fn(SetType $targetType, Type $parameterType): SetType =>
			$this->typeRegistry->set(
				$this->typeRegistry->union([
					$targetType->itemType,
					$parameterType
				]),
				$targetType->range->minLength,
				$targetType->range->maxLength === PlusInfinity::value ?
					PlusInfinity::value : $targetType->range->maxLength + 1
			);
	}

	protected function getExecutor(): callable {
		return function(SetValue $target, Value $parameter): SetValue {
			$values = $target->values;
			$exists = false;
			foreach ($values as $value) {
				if ($value->equals($parameter)) {
					$exists = true;
					break;
				}
			}
			if (!$exists) {
				$values[] = $parameter;
			}
			return $this->valueRegistry->set($values);
		};
	}

}
