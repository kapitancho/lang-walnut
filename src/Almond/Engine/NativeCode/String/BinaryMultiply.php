<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, IntegerType, StringValue, IntegerValue> */
final readonly class BinaryMultiply extends NativeMethod {

	protected function getValidator(): callable {
		return function(StringType $targetType, IntegerType $parameterType, mixed $origin): StringType|ValidationFailure {
			$minValue = $parameterType->numberRange->min;
			if ($minValue !== MinusInfinity::value && $minValue->value >= 0) {
				return $this->typeRegistry->string(
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
		return fn(StringValue $target, IntegerValue $parameter): StringValue =>
			$this->valueRegistry->string(
				str_repeat($target->literalValue, (int)(string)$parameter->literalValue)
			);
	}
}
