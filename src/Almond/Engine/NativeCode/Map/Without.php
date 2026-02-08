<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MapNativeMethod;

/** @extends MapNativeMethod<Type, Type, Value> */
final readonly class Without extends MapNativeMethod {

	protected function getValidator(): callable {
		return fn(MapType $targetType, Type $parameterType): ResultType =>
			$this->typeRegistry->result(
				$this->typeRegistry->map(
					$targetType->itemType,
					max(0, $targetType->range->minLength - 1),
					$targetType->range->maxLength === PlusInfinity::value ?
						PlusInfinity::value : max($targetType->range->maxLength - 1, 0),
					$targetType->keyType
				),
				$this->typeRegistry->core->itemNotFound
			);
	}

	protected function getExecutor(): callable {
		return function(RecordValue $target, Value $parameter): Value {
			$values = $target->values;
			foreach ($values as $key => $value) {
				if ($value->equals($parameter)) {
					unset($values[$key]);
					return $this->valueRegistry->record($values);
				}
			}
			return $this->valueRegistry->error(
				$this->valueRegistry->core->itemNotFound
			);
		};
	}

}
