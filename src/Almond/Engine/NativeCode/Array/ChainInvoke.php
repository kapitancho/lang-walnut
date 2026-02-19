<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, Type, Value> */
final readonly class ChainInvoke extends ArrayNativeMethod {

	/** @return list<Type> */
	protected function getExpectedArrayItemType(): array {
		return [
			$this->typeRegistry->nothing,
			$this->typeRegistry->function(
				$this->typeRegistry->nothing,
				$this->typeRegistry->any
			)
		];
	}

	protected function validateTargetType(Type $targetType, mixed $origin): null|string {
		 /** @var ArrayType $targetType */
		$itemType = $this->toBaseType($targetType->itemType);
		if ($itemType instanceof NothingType) {
			return null;
		}
		if ($itemType instanceof FunctionType) {
			return $itemType->returnType->isSubtypeOf($itemType->parameterType) ? null :
				sprintf(
					"The item type %s is not a valid function type for chainInvoke because its return type is not a subtype of its parameter type",
					$itemType
				);
		}
		return sprintf(
			"The item type %s is not a valid function type for chainInvoke because it is not a function type",
			$itemType
		);
	}

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		/** @var ArrayType $targetType */
		$itemType = $this->toBaseType($targetType->itemType);
		if ($itemType instanceof NothingType) {
			return null;
		}
		/** @var FunctionType $itemType */
		if ($itemType instanceof FunctionType) {
			return $parameterType->isSubtypeOf($itemType->parameterType) ?
				null :
				sprintf(
					"The parameter type %s is not a subtype of the item type parameter type %s",
					$parameterType,
					$itemType->parameterType
				);
		}
		return sprintf(
			"The item type %s is not a valid function type for chainInvoke because it is not a function type",
			$itemType
		);
	}

	protected function getValidator(): callable {
		return function(ArrayType $targetType, Type $parameterType, mixed $origin): Type {
			/** @var NothingType|FunctionType $itemType */
			$itemType = $this->toBaseType($targetType->itemType);
			if ($itemType instanceof NothingType) {
				return $parameterType;
			}
			return $itemType->returnType;
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, Value $parameter): Value {
			foreach ($target->values as $fnValue) {
				if ($fnValue instanceof FunctionValue) {
					$parameter = $fnValue->execute($parameter);
				}
			}
			return $parameter;
		};
	}

}
