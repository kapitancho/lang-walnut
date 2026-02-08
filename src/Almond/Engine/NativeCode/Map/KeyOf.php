<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MapNativeMethod;

/** @extends MapNativeMethod<Type, Type, Value> */
final readonly class KeyOf extends MapNativeMethod {

	protected function getValidator(): callable {
		return fn(MapType $targetType, Type $parameterType): ResultType =>
			$this->typeRegistry->result(
				$targetType->keyType,
				$this->typeRegistry->core->itemNotFound
			);
	}

	protected function getExecutor(): callable {
		return function(RecordValue $target, Value $parameter): Value {
			foreach ($target->values as $key => $value) {
				if ($value->equals($parameter)) {
					return $this->valueRegistry->string($key);
				}
			}
			return $this->valueRegistry->error(
				$this->valueRegistry->core->itemNotFound
			);
		};
	}

}
