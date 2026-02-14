<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ArrayType|TupleType, FunctionType, TupleValue, FunctionValue> */
final readonly class GroupBy extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		return $targetType instanceof ArrayType || $targetType instanceof TupleType;
	}

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof FunctionType) {
				$returnType = $this->toBaseType($parameterType->returnType);
				if ($returnType->isSubtypeOf($this->typeRegistry->string())) {
					if ($type->itemType->isSubtypeOf($parameterType->parameterType)) {
						$minGroupSize = (int)(string)$type->range->minLength > 0 ? 1 : 0;
						$groupArrayType = $this->typeRegistry->array(
							$type->itemType,
							$minGroupSize,
							$type->range->maxLength
						);
						return $this->typeRegistry->map(
							$groupArrayType,
							$minGroupSize,
							$type->range->maxLength,
							$returnType
						);
					}
					return $this->validationFactory->error(
						ValidationErrorType::invalidParameterType,
						sprintf(
							"The parameter type %s of the callback function is not a subtype of %s",
							$type->itemType,
							$parameterType->parameterType
						),
						$origin
					);
				}
				return $this->validationFactory->error(
					ValidationErrorType::invalidParameterType,
					sprintf(
						"The return type of the callback function must be a subtype of String, got %s",
						$returnType
					),
					$origin
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
		return function(TupleValue $target, FunctionValue $parameter): Value {
			$groups = [];
			foreach ($target->values as $value) {
				/** @var StringValue $key */
				$key = $parameter->execute($value);
				$keyStr = $key->literalValue;
				if (!isset($groups[$keyStr])) {
					$groups[$keyStr] = [];
				}
				$groups[$keyStr][] = $value;
			}
			$result = [];
			foreach ($groups as $key => $group) {
				$result[$key] = $this->valueRegistry->tuple($group);
			}
			return $this->valueRegistry->record($result);
		};
	}

}
