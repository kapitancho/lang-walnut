<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, Type, TupleValue> */
final readonly class ZipMap extends ArrayNativeMethod {

	protected function getExpectedArrayItemType(): Type {
		return $this->typeRegistry->string();
	}

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		return $parameterType->isSubtypeOf($this->typeRegistry->array()) ?
			null :
			sprintf(
				"Invalid parameter type: expected an array type, got %s.",
				$parameterType
			);
	}

	protected function getValidator(): callable {
		return function(ArrayType $targetType, Type $parameterType): Type {
			$itemType = $targetType->itemType;
			/** @var ArrayType $parameterType */
			$parameterType = $this->toBaseType($parameterType);
			return $this->typeRegistry->map(
				$parameterType->itemType,
				min(1, $targetType->range->minLength, $parameterType->range->minLength),
				match(true) {
					$targetType->range->maxLength === PlusInfinity::value => $parameterType->range->maxLength,
					$parameterType->range->maxLength === PlusInfinity::value => $targetType->range->maxLength,
					default => min($targetType->range->maxLength, $parameterType->range->maxLength)
				},
				$itemType
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, TupleValue $parameter): Value {
			$values = $target->values;
			$pValues = $parameter->values;
			$result = [];
			foreach ($values as $value) {
				/** @var StringValue $value */
				if (count($pValues) === 0) {
					break;
				}
				$pValue = array_shift($pValues);
				$result[$value->literalValue] = $pValue;
			}
			return $this->valueRegistry->record($result);
		};
	}

}
