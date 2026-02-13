<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, IntegerType, StringValue, IntegerValue> */
final readonly class BinaryIntegerDivide extends NativeMethod {

	protected function getValidator(): callable {
		return function(StringType $targetType, IntegerType $parameterType, mixed $origin): ArrayType|ValidationFailure {
			if (
				$parameterType->numberRange->min !== MinusInfinity::value &&
				$parameterType->numberRange->min->value >= 1
			) {
				return $this->typeRegistry->array(
					$this->typeRegistry->string(
						$parameterType->numberRange->min->value,
						$parameterType->numberRange->max === PlusInfinity::value ?
							PlusInfinity::value : $parameterType->numberRange->max->value
					),
					match(true) {
						$parameterType->numberRange->max === PlusInfinity::value =>
						$targetType->range->minLength > 0 ? 1 : 0,
						default => $targetType->range->minLength->div($parameterType->numberRange->max->value)->floor()
					},
					$targetType->range->maxLength === PlusInfinity::value ?
						PlusInfinity::value :
						$targetType->range->maxLength->div($parameterType->numberRange->min->value)->floor()
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
		return function(StringValue $target, IntegerValue $parameter): TupleValue {
			$splitLength = (int)(string)$parameter->literalValue;
			$result = mb_str_split($target->literalValue, $splitLength);
			if (count($result) > 0 && mb_strlen($result[array_key_last($result)]) < $splitLength) {
				array_pop($result);
			}
			return $this->valueRegistry->tuple(
				array_map(fn(string $piece): StringValue =>
				$this->valueRegistry->string($piece), $result)
			);
		};
	}
}
