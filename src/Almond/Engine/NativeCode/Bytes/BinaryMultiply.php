<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<BytesType, IntegerType, BytesValue, IntegerValue> */
final readonly class BinaryMultiply extends NativeMethod {

	protected function getValidator(): callable {
		return function(BytesType $targetType, IntegerType $parameterType, mixed $origin): BytesType|ValidationFailure {
			$minValue = $parameterType->numberRange->min;
			if ($minValue !== MinusInfinity::value && $minValue->value >= 0) {
				return $this->typeRegistry->bytes(
					$targetType->range->minLength * $minValue->value,
					$targetType->range->maxLength === PlusInfinity::value ||
						$parameterType->numberRange->max === PlusInfinity::value ?
							PlusInfinity::value :
							$targetType->range->maxLength * $parameterType->numberRange->max->value
				);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				$origin
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
