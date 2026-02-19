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
final readonly class BinaryMultiply extends ArrayNativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		return $parameterType->isSubtypeOf($this->typeRegistry->integer(0)) ?
			null : "The parameter type should be a subtype of Integer<0..>";
	}

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, IntegerType $parameterType): Type {
			if ($targetType instanceof TupleType) {
				if ($targetType->restType instanceof NothingType) {
					$minValue = $parameterType->numberRange->min;
					$maxValue = $parameterType->numberRange->max;
					if (
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
			return $this->typeRegistry->array(
				$targetType->itemType,
				$targetType->range->minLength * $minValue->value,
				$targetType->range->maxLength === PlusInfinity::value ||
					$parameterType->numberRange->max === PlusInfinity::value ?
						PlusInfinity::value :
						$targetType->range->maxLength * $parameterType->numberRange->max->value
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
