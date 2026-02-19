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
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, IntegerType, IntegerValue> */
final readonly class Take extends ArrayNativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		return $parameterType->isSubtypeOf($this->typeRegistry->integer(0)) ? null : sprintf(
			"Parameter type %s is not a subtype Integer<0..>",
			$parameterType
		);
	}

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, IntegerType $parameterType): Type {
			if ($targetType instanceof TupleType && $parameterType instanceof IntegerSubsetType && count($parameterType->subsetValues) === 1) {
				$param = (int)(string)$parameterType->subsetValues[0];
				return $this->typeRegistry->tuple(
					array_slice($targetType->types, 0, $param),
					$param > count($targetType->types) ? $targetType->restType : $this->typeRegistry->nothing
				);
			}
			$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
			$maxLength = match(true) {
				$parameterType->numberRange->max === PlusInfinity::value => $type->range->maxLength,
				$type->range->maxLength === PlusInfinity::value,
				$type->range->maxLength > $parameterType->numberRange->max->value => $parameterType->numberRange->max->value,
				default => $type->range->maxLength,
			};
			return $this->typeRegistry->array(
				$type->itemType,
				min($parameterType->numberRange->min->value, $type->range->minLength),
				$maxLength
			);
		};
	}

	protected function getExecutor(): callable {
		return fn(TupleValue $target, IntegerValue $parameter): TupleValue =>
			$this->valueRegistry->tuple(
				array_slice($target->values, 0, (int)(string)$parameter->literalValue)
			);
	}

}
