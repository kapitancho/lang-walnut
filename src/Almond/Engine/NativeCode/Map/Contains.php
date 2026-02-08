<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MapNativeMethod;

/** @extends MapNativeMethod<Type, Type, Value> */
final readonly class Contains extends MapNativeMethod {

	protected function getValidator(): callable {
		return fn(MapType $targetType, Type $parameterType): BooleanType =>
			$this->typeRegistry->boolean;
	}

	protected function getExecutor(): callable {
		return function(RecordValue $target, Value $parameter): BooleanValue {
			if (array_any($target->values, fn($value) => $value->equals($parameter))) {
				return $this->valueRegistry->true;
			}
			return $this->valueRegistry->false;
		};
	}

}
