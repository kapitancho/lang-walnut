<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ArrayType|TupleType, RecordType, TupleValue, RecordValue> */
final readonly class PadRight extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		return $targetType instanceof ArrayType || $targetType instanceof TupleType;
	}

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof RecordType) {
				$types = $parameterType->types;
				$lengthType = $types['length'] ?? null;
				if ($lengthType) {
					$lengthType = $this->toBaseType($lengthType);
				}
				$valueType = $types['value'] ?? null;
				if ($lengthType instanceof IntegerType) {
					return $this->typeRegistry->array(
						$this->typeRegistry->union([
							$type->itemType,
							$valueType
						]),
						max(
							(int)(string)$type->range->minLength,
							$lengthType->numberRange->min === MinusInfinity::value ?
								0 : $lengthType->numberRange->min->value
						),
						$type->range->maxLength === PlusInfinity::value ||
							$lengthType->numberRange->max === PlusInfinity::value ? PlusInfinity::value :
							max(
								(int)(string)$type->range->maxLength,
								$lengthType->numberRange->max->value -
								($lengthType->numberRange->max->inclusive ? 0 : 1)
							),
					);
				}
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				$origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, RecordValue $parameter): TupleValue {
			$values = $target->values;
			$paramValues = $parameter->values;
			/** @var IntegerValue $length */
			$length = $paramValues['length'];
			$padValue = $paramValues['value'];
			return $this->valueRegistry->tuple(
				array_pad(
					$values,
					(int)(string)$length->literalValue,
					$padValue
				)
			);
		};
	}

}
