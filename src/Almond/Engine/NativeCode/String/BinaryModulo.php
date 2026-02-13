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
final readonly class BinaryModulo extends NativeMethod {

	protected function getValidator(): callable {
		return function(StringType $targetType, IntegerType $parameterType, mixed $origin): StringType|ValidationFailure {
			if (
				$parameterType->numberRange->min !== MinusInfinity::value &&
				$parameterType->numberRange->min->value >= 1
			) {
				if (
					$targetType->range->maxLength !== PlusInfinity::value &&
					$parameterType->numberRange->min->value > $targetType->range->maxLength
				) {
					return $targetType;
				}
				if (
					$targetType->range->maxLength !== PlusInfinity::value &&
					(string)$targetType->range->minLength === (string)$targetType->range->maxLength &&
					$parameterType->numberRange->max !== PlusInfinity::value &&
					(string)$parameterType->numberRange->max->value === (string)$parameterType->numberRange->min->value
				) {
					$size = $targetType->range->maxLength->mod($parameterType->numberRange->min->value);
					return $this->typeRegistry->string($size, $size);
				}

				return $this->typeRegistry->string(
					0,
					match(true) {
						$parameterType->numberRange->max === PlusInfinity::value => $targetType->range->maxLength,
						$targetType->range->maxLength === PlusInfinity::value => max(
							0,
							$parameterType->numberRange->max->value->sub(1),
						),
						default => max(
							0,
							min(
								$parameterType->numberRange->max->value->sub(1),
								$targetType->range->maxLength
							)
						)
					}
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
		return function(StringValue $target, IntegerValue $parameter): StringValue {
			$splitLength = (int)(string)$parameter->literalValue;
			$result = mb_str_split($target->literalValue, $splitLength);
			$last = $result[array_key_last($result)] ?? '';
			return $this->valueRegistry->string(
				mb_strlen($last) < $splitLength ? $last : ''
			);
		};
	}
}
