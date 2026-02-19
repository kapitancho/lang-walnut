<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, Type, TupleValue> */
final readonly class ZipMap extends ArrayNativeMethod {

	protected function validateTargetArrayItemType(Type $itemType, mixed $origin): null|string {
		return $itemType->isSubtypeOf($this->typeRegistry->string()) ? null :
			sprintf(
				"Invalid target array item type: expected a subtype of String, got %s.",
				$itemType
			);
	}

	protected function validateParameterType(Type $parameterType, Type $targetType, mixed $origin): null|string|ValidationFailure {
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
			$parameterType = $this->toBaseType($parameterType);
			$pType = $parameterType instanceof TupleType ? $parameterType->asArrayType() : $parameterType;
			/** @var ArrayType $pType */
			return $this->typeRegistry->map(
				$pType->itemType,
				min(1, $targetType->range->minLength, $pType->range->minLength),
				match(true) {
					$targetType->range->maxLength === PlusInfinity::value => $pType->range->maxLength,
					$pType->range->maxLength === PlusInfinity::value => $targetType->range->maxLength,
					default => min($targetType->range->maxLength, $pType->range->maxLength)
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
