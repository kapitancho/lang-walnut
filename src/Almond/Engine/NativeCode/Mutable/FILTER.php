<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<MutableType, FunctionType, MutableValue, FunctionValue> */
final readonly class FILTER extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		if ($targetType instanceof MutableType) {
			$type = $this->toBaseType($targetType->valueType);
			if (($type instanceof ArrayType || $type instanceof MapType || $type instanceof SetType) && $type->isSubtypeOf(
				$this->typeRegistry->union([
					$this->typeRegistry->array($type->itemType),
					$this->typeRegistry->map($type->itemType),
					$this->typeRegistry->set($type->itemType),
				])
			) &&
				!$type->isSubtypeOf($this->typeRegistry->array($this->typeRegistry->any, 1)) &&
				!$type->isSubtypeOf($this->typeRegistry->map($this->typeRegistry->any, 1)) &&
				!$type->isSubtypeOf($this->typeRegistry->set($this->typeRegistry->any, 1))
			) {
				return true;
			}
		}
		return false;
	}

	protected function getValidator(): callable {
		return function(MutableType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$type = $this->toBaseType($targetType->valueType);
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof FunctionType && $parameterType->returnType->isSubtypeOf($this->typeRegistry->boolean)) {
				if ($type->itemType->isSubtypeOf($parameterType->parameterType)) {
					return $targetType;
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
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				$origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(MutableValue $target, FunctionValue $parameter): MutableValue {
			$v = $target->value;
			if ($v instanceof TupleValue || $v instanceof RecordValue || $v instanceof SetValue) {
				$values = $v->values;
				$result = [];
				$true = $this->valueRegistry->true;
				foreach($values as $key => $value) {
					$r = $parameter->execute($value);
					if ($true->equals($r)) {
						$result[$key] = $value;
					}
				}
				if (!$v instanceof RecordValue) {
					$result = array_values($result);
				}
				$target->value = match(true) {
					$v instanceof TupleValue => $this->valueRegistry->tuple($result),
					$v instanceof RecordValue => $this->valueRegistry->record($result),
					$v instanceof SetValue => $this->valueRegistry->set($result),
				};
				return $target;
			}
			return $target;
		};
	}

}
