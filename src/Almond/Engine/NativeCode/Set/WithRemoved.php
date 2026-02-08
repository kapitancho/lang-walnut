<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\SetNativeMethod;

/** @extends SetNativeMethod<Type, Value> */
final readonly class WithRemoved extends SetNativeMethod {

	protected function getValidator(): callable {
		return fn(SetType $targetType, Type $parameterType): Type =>
			$this->typeRegistry->result(
				$this->typeRegistry->set(
					$targetType->itemType,
					max(0, $targetType->range->minLength - 1),
					$targetType->range->maxLength === PlusInfinity::value ?
						PlusInfinity::value : max($targetType->range->maxLength - 1, 0)
				),
				$this->typeRegistry->core->itemNotFound
			);
	}

	protected function getExecutor(): callable {
		return function(SetValue $target, Value $parameter): Value {
			$values = $target->valueSet;
			$key = (string)$parameter;
			if (array_key_exists($key, $values)) {
				unset($values[$key]);
				return $this->valueRegistry->set(array_values($values));
			}
			return $this->valueRegistry->error(
				$this->valueRegistry->core->itemNotFound
			);
		};
	}

}
