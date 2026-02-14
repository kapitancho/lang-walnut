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
final readonly class BinaryModulo extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		return $targetType instanceof ArrayType || $targetType instanceof TupleType;
	}

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, IntegerType $parameterType, mixed $origin): Type|ValidationFailure {
			if (
				$targetType instanceof TupleType &&
				$targetType->restType instanceof NothingType &&
				$parameterType->numberRange->min !== MinusInfinity::value &&
				$parameterType->numberRange->min->value > 0 &&
				$parameterType->numberRange->max !== PlusInfinity::value &&
				(string)$parameterType->numberRange->max->value === (string)$parameterType->numberRange->min->value
			) {
				$l = (int)(string)$parameterType->numberRange->max->value;
				$skip = intdiv(count($targetType->types), $l) * $l;
				return $this->typeRegistry->tuple(
					array_slice(
						$targetType->types,
						$skip
					),
					null
				);
			}
			$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
			if (
				$parameterType->numberRange->min !== MinusInfinity::value &&
				$parameterType->numberRange->min->value > 0
			) {
				if (
					$type->range->maxLength !== PlusInfinity::value &&
					$parameterType->numberRange->min->value > $type->range->maxLength
				) {
					return $targetType;
				}
				if (
					$type->range->maxLength !== PlusInfinity::value &&
					(string)$type->range->minLength === (string)$type->range->maxLength &&
					$parameterType->numberRange->max !== PlusInfinity::value &&
					(string)$parameterType->numberRange->max->value === (string)$parameterType->numberRange->min->value
				) {
					$size = $type->range->maxLength->mod($parameterType->numberRange->min->value);
					return $this->typeRegistry->array($type->itemType, $size, $size);
				}
				return $this->typeRegistry->array(
					$type->itemType,
					0,
					match(true) {
						$parameterType->numberRange->max === PlusInfinity::value => $type->range->maxLength,
						$type->range->maxLength === PlusInfinity::value => max(
							0,
							$parameterType->numberRange->max->value->sub(1),
						),
						default => max(
							0,
							min(
								$parameterType->numberRange->max->value->sub(1),
								$type->range->maxLength
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
		return function(TupleValue $target, IntegerValue $parameter): TupleValue {
			$chunkSize = (int)(string)$parameter->literalValue;
			$chunks = array_chunk($target->values, $chunkSize);
			$last = $chunks[array_key_last($chunks)];

			return $this->valueRegistry->tuple(
				count($chunks) > 0 && count($last) < $chunkSize ?
					$last : []
			);
		};
	}

}
