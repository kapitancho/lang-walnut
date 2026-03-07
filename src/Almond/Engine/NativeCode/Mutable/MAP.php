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

/** @extends MutableNativeMethod<Type, FunctionType, FunctionValue> */
final readonly class MAP extends MutableNativeMethod {

	protected function validateTargetType(Type $targetType, mixed $origin): null|string {
		/** @var MutableType $targetType */
		$type = $this->toBaseType($targetType->valueType);
		if (($type instanceof ArrayType || $type instanceof MapType || $type instanceof SetType) && $type->isSubtypeOf(
			$this->typeRegistry->union([
				$this->typeRegistry->array($type->itemType),
				$this->typeRegistry->map($type->itemType),
				$this->typeRegistry->set($type->itemType),
			])
		)) {
			return $type->isSubtypeOf($this->typeRegistry->set($this->typeRegistry->any, 2)) ?
				 sprintf("Only Set type with a minimum number of elements 0 or 1 are allowed, got %s",
					 $targetType->valueType
				 ) : null;
		}
		return sprintf("The value type of the target set must be a subtype of Array, Map or Set, got %s",
			$targetType->valueType
		);
	}

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		/** @var MutableType $targetType */
		/** @var FunctionType $parameterType */
		/** @var ArrayType|MapType|SetType $type */
		$type = $this->toBaseType($targetType->valueType);
		if (!$type->itemType->isSubtypeOf($parameterType->parameterType)) {
			return sprintf(
				"The parameter type %s of the callback function is not a subtype of %s",
				$type->itemType,
				$parameterType->parameterType
			);
		}
		return $parameterType->returnType->isSubtypeOf($type->itemType) ? null : sprintf(
			"The value type %s is not a subtype of the return type %s of the callback function",
			$parameterType->returnType,
			$type->itemType,
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
				foreach($values as $key => $value) {
					$r = $parameter->execute($value);
					$result[$key] = $r;
				}
				if (!$v instanceof RecordValue) {
					$result = array_values($result);
				}
				$target->value = match(true) {
					$v instanceof TupleValue => $this->valueRegistry->tuple($result),
					$v instanceof RecordValue =>
						/** @phpstan-ignore argument.type */
						$this->valueRegistry->record($result),
					$v instanceof SetValue => $this->valueRegistry->set($result),
				};
				return $target;
			}
			return $target;
		};
	}

}
