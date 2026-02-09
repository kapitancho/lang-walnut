<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TrueType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MapNativeMethod;

/** @extends MapNativeMethod<Type, NullType, NullValue> */
final readonly class AsBoolean extends MapNativeMethod {

	protected function getValidator(): callable {
		return fn(MapType $targetType, NullType $parameterType): BooleanType|TrueType|FalseType =>
			match(true) {
				$targetType->range->minLength > 0 => $this->typeRegistry->true,
				$targetType->range->maxLength !== PlusInfinity::value &&
				(string)$targetType->range->maxLength == '0' => $this->typeRegistry->false,
				default => $this->typeRegistry->boolean,
			};
	}

	protected function getExecutor(): callable {
		return fn(RecordValue $target, NullValue $parameter): BooleanValue =>
			$this->valueRegistry->boolean(count($target->values) > 0);
	}

}
