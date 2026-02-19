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

	protected function validateTargetValueType(Type $valueType): null|string {
		return $valueType instanceof StringType && $valueType->range->maxLength === PlusInfinity::value ?
			null : sprintf("The value type of the target set must be an unbounded String type, got %s",
				$valueType
			);
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
