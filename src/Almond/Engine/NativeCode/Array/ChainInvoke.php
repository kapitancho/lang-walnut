<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, Type, Value> */
final readonly class ChainInvoke extends ArrayNativeMethod {

	protected function isTargetItemTypeValid(Type $targetItemType, mixed $origin): bool {
		if ($targetItemType instanceof NothingType) {
			return true;
		}
		if ($targetItemType instanceof FunctionType) {
			return $targetItemType->returnType->isSubtypeOf($targetItemType->parameterType);
		}
		return false;
	}

	protected function getValidator(): callable {
		return function(ArrayType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$itemType = $this->toBaseType($targetType->itemType);
			if ($itemType instanceof NothingType) {
				return $parameterType;
			}
			/** @var FunctionType $itemType */
			if ($parameterType->isSubtypeOf($itemType->parameterType)) {
				return $itemType->returnType;
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf(
					"The parameter type %s is not a subtype of %s",
					$parameterType,
					$itemType->parameterType
				),
				origin: $origin
			);
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
