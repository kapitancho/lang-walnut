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
final readonly class Drop extends ArrayNativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		return $parameterType->isSubtypeOf($this->typeRegistry->integer(0)) ? null : sprintf(
			"Parameter type %s is not a subtype Integer<0..>",
			$parameterType
		);
	}

	protected function getValidator(): callable {
		return function(ArrayType $targetType, IntegerType $parameterType): Type {
			if ($targetType instanceof TupleType && $parameterType instanceof IntegerSubsetType && count($parameterType->subsetValues) === 1) {
				$param = (int)(string)$parameterType->subsetValues[0];
				return $this->typeRegistry->tuple(
					array_slice($targetType->types, $param),
					$targetType->restType
				);
			}
			$minLength = match(true) {
				$parameterType->numberRange->max === PlusInfinity::value,
				$parameterType->numberRange->max->value > $targetType->range->minLength => 0,
				default => $targetType->range->minLength->sub($parameterType->numberRange->max->value)
			};
			$maxLength = match(true) {
				$targetType->range->maxLength === PlusInfinity::value => PlusInfinity::value,
				$parameterType->numberRange->min->value > $targetType->range->maxLength => 0,
				default => $targetType->range->maxLength->sub($parameterType->numberRange->min->value)
			};
			return $this->typeRegistry->array(
				$targetType->itemType,
				$minLength,
				$maxLength
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
