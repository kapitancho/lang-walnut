<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ArrayType|TupleType, IntegerType, TupleValue, IntegerValue> */
final readonly class BinaryMultiply extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		return $targetType instanceof ArrayType || $targetType instanceof TupleType;
	}

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, IntegerType $parameterType, mixed $origin): Type|ValidationFailure {
			if ($targetType instanceof TupleType) {
				if ($targetType->restType instanceof NothingType) {
					$minValue = $parameterType->numberRange->min;
					$maxValue = $parameterType->numberRange->max;
					if (
						$minValue !== MinusInfinity::value && $minValue->value >= 0 &&
						$maxValue !== PlusInfinity::value && (string)$maxValue->value === (string)$minValue->value
					) {
						$result = [];
						for ($i = 0; $i < $minValue->value; $i++) {
							$result = array_merge($result, $targetType->types);
						}
						return $this->typeRegistry->tuple($result, null);
					}
				}
				$targetType = $targetType->asArrayType();
			}
			$minValue = $parameterType->numberRange->min;
			if ($minValue !== MinusInfinity::value && $minValue->value >= 0) {
				return $this->typeRegistry->array(
					$targetType->itemType,
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
		return function(TupleValue $target, IntegerValue $parameter): TupleValue {
			$result = [];
			for ($i = 0; $i < (int)(string)$parameter->literalValue; $i++) {
				$result = array_merge($result, $target->values);
			}
			return $this->valueRegistry->tuple($result);
		};
	}

}
