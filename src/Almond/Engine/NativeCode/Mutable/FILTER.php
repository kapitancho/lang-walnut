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
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MutableNativeMethod;

/** @extends MutableNativeMethod<ArrayType|SetType|MapType, FunctionType, FunctionValue> */
final readonly class FILTER extends MutableNativeMethod {

	protected function validateTargetValueType(Type $valueType): null|string {
		if (($valueType instanceof ArrayType || $valueType instanceof MapType || $valueType instanceof SetType) && $valueType->isSubtypeOf(
			$this->typeRegistry->union([
				$this->typeRegistry->array($valueType->itemType),
				$this->typeRegistry->map($valueType->itemType),
				$this->typeRegistry->set($valueType->itemType),
			])
		) &&
			!$valueType->isSubtypeOf($this->typeRegistry->array($this->typeRegistry->any, 1)) &&
			!$valueType->isSubtypeOf($this->typeRegistry->map($this->typeRegistry->any, 1)) &&
			!$valueType->isSubtypeOf($this->typeRegistry->set($this->typeRegistry->any, 1))
		) {
			return null;
		}
		return sprintf("The value type of the target set must be a subtype of Array, Map or Set with a minimum number of elements 0, got %s",
			$valueType
		);
	}

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		/** @var MutableType $targetType */
		if (!$parameterType instanceof FunctionType) {
			return sprintf("The parameter type must be a function type, got %s", $parameterType);
		}
		if (!$parameterType->returnType->isSubtypeOf($this->typeRegistry->boolean)) {
			return sprintf("The return type of the callback function must be a subtype of Boolean, got %s", $parameterType->returnType);
		}
		/** @var ArrayType|SetType|MapType $type */
		$type = $this->toBaseType($targetType->valueType);
		return $type->itemType->isSubtypeOf($parameterType->parameterType) ?
			null :
			sprintf(
				"The parameter type %s of the callback function is not a subtype of %s",
				$type->itemType,
				$parameterType->parameterType
			);
	}

	protected function getValidator(): callable {
		return fn(MutableType $targetType, FunctionType $parameterType, mixed $origin): MutableType => $targetType;
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
