<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, IntegerType, StringValue, IntegerValue> */
final readonly class BinaryModulo extends NativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		/** @var IntegerType $parameterType */
		return $parameterType->numberRange->min !== MinusInfinity::value &&
		$parameterType->numberRange->min->value >= 1 ?
			null : sprintf(
				"Expected a subtype of Integer<1..>, but got %s",
				$parameterType
			);
	}

	protected function getValidator(): callable {
		return function(StringType $targetType, IntegerType $parameterType): StringType {
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
		};
	}

	protected function getExecutor(): callable {
		return function(StringValue $target, IntegerValue $parameter): StringValue {
			/** @var int<1, max> $splitLength */
			$splitLength = (int)(string)$parameter->literalValue;
			$result = mb_str_split($target->literalValue, $splitLength);
			$last = $result[array_key_last($result)] ?? '';
			return $this->valueRegistry->string(
				mb_strlen($last) < $splitLength ? $last : ''
			);
		};
	}
}
