<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ArrayType|TupleType, IntegerType, TupleValue, IntegerValue> */
final readonly class BinaryModulo extends NativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType, mixed $origin): null|string|ValidationFailure {
		return $parameterType->isSubtypeOf($this->typeRegistry->integer(1)) ?
			null : "The parameter type should be a subtype of Integer<1..>";
	}

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, IntegerType $parameterType): Type {
			if (
				$targetType instanceof TupleType &&
				$targetType->restType instanceof NothingType &&
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
