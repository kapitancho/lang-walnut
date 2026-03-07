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
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, IntegerType, IntegerValue> */
final readonly class BinaryModulo extends ArrayNativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		return $parameterType->isSubtypeOf($this->typeRegistry->integer(1)) ?
			null : "The parameter type should be a subtype of Integer<1..>";
	}

	protected function getValidator(): callable {
		return function(ArrayType $targetType, IntegerType $parameterType): Type {
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
				return $this->typeRegistry->array($targetType->itemType, $size, $size);
			}
			return $this->typeRegistry->array(
				$targetType->itemType,
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
