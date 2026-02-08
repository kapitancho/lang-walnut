<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MapNativeMethod;

/** @extends MapNativeMethod<AnyType, NullType, NullValue> */
final readonly class Length extends MapNativeMethod {

	protected function getValidator(): callable {
		return fn(MapType $targetType, NullType $parameterType, Expression|null $origin) =>
			$this->typeRegistry->integer(
				$targetType->range->minLength,
				$targetType->range->maxLength
			);
	}

	protected function getExecutor(): callable {
		return fn(RecordValue $target, NullValue $parameter) =>
			$this->valueRegistry->integer(count($target->values));
	}

}
