<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ArrayType|TupleType, IntegerType, TupleValue, IntegerValue> */
final readonly class Drop extends NativeMethod {

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, IntegerType $parameterType, mixed $origin): Type|ValidationFailure {
			if ($targetType instanceof TupleType && $parameterType instanceof IntegerSubsetType && count($parameterType->subsetValues) === 1) {
				$param = (int)(string)$parameterType->subsetValues[0];
				if ($param >= 0) {
					return $this->typeRegistry->tuple(
						array_slice($targetType->types, $param),
						$targetType->restType
					);
				}
			}
			$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
			if ($parameterType->isSubtypeOf($this->typeRegistry->integer(0))) {
				$minLength = match(true) {
					$parameterType->numberRange->max === PlusInfinity::value,
					$parameterType->numberRange->max->value > $type->range->minLength => 0,
					default => $type->range->minLength->sub($parameterType->numberRange->max->value)
				};
				$maxLength = match(true) {
					$type->range->maxLength === PlusInfinity::value => PlusInfinity::value,
					$parameterType->numberRange->min->value > $type->range->maxLength => 0,
					default => $type->range->maxLength->sub($parameterType->numberRange->min->value)
				};
				return $this->typeRegistry->array(
					$type->itemType,
					$minLength,
					$maxLength
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
		return fn(TupleValue $target, IntegerValue $parameter): TupleValue =>
			$this->valueRegistry->tuple(
				array_slice($target->values, (int)(string)$parameter->literalValue)
			);
	}

}
