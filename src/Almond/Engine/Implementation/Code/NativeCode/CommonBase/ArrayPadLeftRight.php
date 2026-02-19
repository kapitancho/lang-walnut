<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, RecordType, RecordValue> */
abstract readonly class ArrayPadLeftRight extends ArrayNativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType, mixed $origin): null|string|ValidationFailure {
		return $parameterType->isSubtypeOf(
			$this->typeRegistry->record([
				"length" => $this->typeRegistry->integer(0),
				"value" => $this->typeRegistry->any,
			], null)
		) ? null : sprintf(
			"Parameter type %s is not a subtype [length: Integer<0..>, value: Any]",
			$parameterType
		);
	}

	protected function getValidator(): callable {
		return function(ArrayType $targetType, RecordType $parameterType): Type {
			$types = $parameterType->types;
			$lengthType = $this->toBaseType($types['length']);
			$valueType = $types['value'];
			/** @var IntegerType $lengthType */
			return $this->typeRegistry->array(
				$this->typeRegistry->union([
					$targetType->itemType,
					$valueType
				]),
				max(
					(int)(string)$targetType->range->minLength,
					$lengthType->numberRange->min === MinusInfinity::value ?
						0 : $lengthType->numberRange->min->value
				),
				$targetType->range->maxLength === PlusInfinity::value ||
				$lengthType->numberRange->max === PlusInfinity::value ? PlusInfinity::value :
					max(
						(int)(string)$targetType->range->maxLength,
						$lengthType->numberRange->max->value -
						($lengthType->numberRange->max->inclusive ? 0 : 1)
					),
			);
		};
	}

}