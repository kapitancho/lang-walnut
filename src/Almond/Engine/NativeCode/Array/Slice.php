<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\UnknownProperty;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\OptionalKeyType as OptionalKeyTypeImpl;

/** @extends NativeMethod<ArrayType|TupleType, RecordType, TupleValue, RecordValue> */
final readonly class Slice extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		return $targetType instanceof ArrayType || $targetType instanceof TupleType;
	}

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$targetType = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
			$pInt = $this->typeRegistry->integer(0);
			$pType = $this->typeRegistry->record([
				"start" => $pInt,
				"length" => new OptionalKeyTypeImpl($pInt)
			], null);
			if ($parameterType->isSubtypeOf($pType)) {
				$parameterType = $this->toBaseType($parameterType);
				if ($parameterType instanceof RecordType) {
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
