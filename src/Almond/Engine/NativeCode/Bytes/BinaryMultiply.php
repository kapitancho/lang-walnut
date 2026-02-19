<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<BytesType, IntegerType, BytesValue, IntegerValue> */
final readonly class BinaryMultiply extends NativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		return $parameterType->isSubtypeOf($this->typeRegistry->integer(0)) ?
			null :
			sprintf("Parameter type %s is not a subtype of Integer<0..>", $parameterType);
	}

	protected function getValidator(): callable {
		return function(BytesType $targetType, IntegerType $parameterType): BytesType {
			return $this->typeRegistry->bytes(
				$targetType->range->minLength * $parameterType->numberRange->min->value,
				$targetType->range->maxLength === PlusInfinity::value ||
					$parameterType->numberRange->max === PlusInfinity::value ?
						PlusInfinity::value :
						$targetType->range->maxLength * $parameterType->numberRange->max->value
			);
		};
	}

	protected function getExecutor(): callable {
		return fn(BytesValue $target, IntegerValue $parameter): BytesValue =>
			$this->valueRegistry->bytes(
				str_repeat($target->literalValue, (int)(string)$parameter->literalValue)
			);
	}
}
