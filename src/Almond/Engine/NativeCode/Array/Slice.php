<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\UnknownProperty;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\OptionalKeyType as OptionalKeyTypeImpl;

/** @extends ArrayNativeMethod<Type, RecordType, RecordValue> */
final readonly class Slice extends ArrayNativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType, mixed $origin): null|string|ValidationFailure {
		$expectedType = $this->typeRegistry->record([
			"start" => $this->typeRegistry->integer(0),
			"length" => new OptionalKeyTypeImpl($this->typeRegistry->integer(0)),
		], null);
		return $parameterType->isSubtypeOf($expectedType) ? null : sprintf(
			"Parameter type %s is not a subtype %s",
			$parameterType,
			$expectedType
		);
	}

	protected function getValidator(): callable {
		return function(ArrayType $targetType, RecordType $parameterType): Type {
			return $this->typeRegistry->array(
				$targetType->itemType,
				0,
				min(
					$targetType->range->maxLength,
					($l = $parameterType->types['length'] ?? null) ?
						$this->toBaseType($l)->numberRange->max->value :
						$targetType->range->maxLength
				)
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, RecordValue $parameter): TupleValue {
			$values = $target->values;
			$start = $parameter->valueOf('start');

			$length = $parameter->valueOf('length');
			if ($length instanceof UnknownProperty) {
				$length = $this->valueRegistry->integer(count($values));
			}
			/** @var IntegerValue $start */
			/** @var IntegerValue $length */
			return $this->valueRegistry->tuple(
				array_slice(
					$values,
					(int)(string)$start->literalValue,
					(int)(string)$length->literalValue
				)
			);
		};
	}

}
