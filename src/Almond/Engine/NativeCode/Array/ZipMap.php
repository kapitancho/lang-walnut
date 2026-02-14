<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ArrayType|TupleType, ArrayType|TupleType, TupleValue, TupleValue> */
final readonly class ZipMap extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		if (!($targetType instanceof ArrayType || $targetType instanceof TupleType)) {
			return false;
		}
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		return $type->itemType->isSubtypeOf($this->typeRegistry->string());
	}

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
			$itemType = $type->itemType;
			$parameterType = $this->toBaseType($parameterType);
			$pType = $parameterType instanceof TupleType ? $parameterType->asArrayType() : $parameterType;
			if ($pType instanceof ArrayType) {
				return $this->typeRegistry->map(
					$pType->itemType,
					min(1, $type->range->minLength, $pType->range->minLength),
					match(true) {
						$type->range->maxLength === PlusInfinity::value => $pType->range->maxLength,
						$pType->range->maxLength === PlusInfinity::value => $type->range->maxLength,
						default => min($type->range->maxLength, $pType->range->maxLength)
					},
					$itemType
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
