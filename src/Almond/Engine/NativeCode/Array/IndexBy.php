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
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ArrayType|TupleType, FunctionType, TupleValue, FunctionValue> */
final readonly class IndexBy extends NativeMethod {

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
						$maxLength = $type->range->maxLength;
						$minLength = (string)$type->range->minLength === '0' ||
							($maxLength !== PlusInfinity::value && (string)$maxLength === '0') ? 0 : $type->range->minLength;
						return $this->typeRegistry->map(
							$type->itemType,
							$minLength,
							$maxLength,
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
			$result = [];
			foreach ($target->values as $value) {
				/** @var StringValue $key */
				$key = $parameter->execute($value);
				$result[$key->literalValue] = $value;
			}
			return $this->valueRegistry->record($result);
		};
	}

}
