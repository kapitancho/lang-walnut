<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MutableNativeMethod;

/** @extends MutableNativeMethod<StringType, StringType, StringValue> */
final readonly class APPEND extends MutableNativeMethod {

	protected function isTargetValueTypeValid(Type $targetValueType, mixed $origin): bool {
		return $targetValueType instanceof StringType && $targetValueType->range->maxLength === PlusInfinity::value;
	}

	protected function getValidator(): callable {
		return fn(MutableType $targetType, StringType $parameterType): MutableType =>
			$targetType;
	}

	protected function getExecutor(): callable {
		return function(MutableValue $target, StringValue $parameter): MutableValue {
			/** @var StringValue $targetValue */
			$targetValue = $target->value;
			$target->value = $this->valueRegistry->string($targetValue->literalValue . $parameter->literalValue);
			return $target;
		};
	}

}
