<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<MutableType, FunctionType, MutableValue, FunctionValue> */
final readonly class REMAPKEYS extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type|ValidationFailure {
		if ($targetType instanceof MutableType) {
			$valueType = $this->toBaseType($targetType->valueType);
			if ($valueType instanceof MapType) {
				if ($valueType->range->minLength > 1) {
					return $this->validationFactory->error(
						ValidationErrorType::invalidTargetType,
						"Invalid target type: REMAPKEYS can only be used on maps with a minimum size of 0 or 1",
						$origin
					);
				}
				return true;
			}
		}
		return false;
	}

	protected function getValidator(): callable {
		return function(MutableType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$valueType = $this->toBaseType($targetType->valueType);
			$parameterType = $this->toBaseType($parameterType);
			if ($valueType instanceof MapType && $parameterType instanceof FunctionType) {
				if ($valueType->keyType->isSubtypeOf($parameterType->parameterType)) {
					$r = $parameterType->returnType;
					$returnType = $r instanceof ResultType ? $r->returnType : $r;
					if ($returnType->isSubtypeOf($valueType->keyType)) {
						return $targetType;
					}
					return $this->validationFactory->error(
						ValidationErrorType::invalidReturnType,
						sprintf(
							"The return type %s of the callback function is not a subtype of %s",
							$returnType,
							$valueType->keyType
						),
						$origin
					);
				}
				return $this->validationFactory->error(
					ValidationErrorType::invalidParameterType,
					sprintf(
						"The parameter type %s of the callback function is not a supertype of %s",
						$parameterType->parameterType,
						$valueType->keyType,
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
			if ($v instanceof RecordValue) {
				$values = $v->values;
				$result = [];
				foreach($values as $key => $value) {
					$r = $parameter->execute(
						$this->valueRegistry->string($key)
					);
					if ($r instanceof StringValue) {
						$result[$r->literalValue] = $value;
					}
				}
				$target->value = $this->valueRegistry->record($result);
				return $target;
			}
			return $target;
		};
	}

}
