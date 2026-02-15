<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, FunctionType, FunctionValue> */
final readonly class IndexBy extends ArrayNativeMethod {

	protected function getValidator(): callable {
		return function(ArrayType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof FunctionType) {
				$returnType = $this->toBaseType($parameterType->returnType);
				if ($returnType->isSubtypeOf($this->typeRegistry->string())) {
					if ($targetType->itemType->isSubtypeOf($parameterType->parameterType)) {
						$maxLength = $targetType->range->maxLength;
						$minLength = (string)$targetType->range->minLength === '0' ||
							($maxLength !== PlusInfinity::value && (string)$maxLength === '0') ? 0 : $targetType->range->minLength;
						return $this->typeRegistry->map(
							$targetType->itemType,
							$minLength,
							$maxLength,
							$returnType
						);
					}
					return $this->validationFactory->error(
						ValidationErrorType::invalidParameterType,
						sprintf(
							"The parameter type %s of the callback function is not a subtype of %s",
							$targetType->itemType,
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
