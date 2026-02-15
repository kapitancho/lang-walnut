<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, FunctionType, FunctionValue> */
final readonly class FlatMap extends ArrayNativeMethod {

	protected function getValidator(): callable {
		return function(ArrayType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof FunctionType) {
				if ($targetType->itemType->isSubtypeOf($parameterType->parameterType)) {
					$r = $parameterType->returnType;
					$errorType = $r instanceof ResultType ? $r->errorType : null;
					$returnType = $r instanceof ResultType ? $r->returnType : $r;

					if ($returnType->isSubtypeOf($this->typeRegistry->array())) {
						$returnType = $this->toBaseType($returnType);
						if ($returnType instanceof TupleType) {
							$returnType = $returnType->asArrayType();
						}
						$minLength = ((int)(string)$targetType->range->minLength) * ((int)(string)$returnType->range->minLength);
						$maxLength = $targetType->range->maxLength === PlusInfinity::value ||
							$returnType->range->maxLength === PlusInfinity::value ?
							PlusInfinity::value :
							((int)(string)$targetType->range->maxLength) * ((int)(string)$returnType->range->maxLength);

						$resultArray = $this->typeRegistry->array(
							$returnType->itemType,
							$minLength,
							$maxLength
						);
						return $errorType ? $this->typeRegistry->result($resultArray, $errorType) : $resultArray;
					}
					return $this->validationFactory->error(
						ValidationErrorType::invalidParameterType,
						sprintf(
							"The return type of the callback function must be a subtype of Array, got %s",
							$returnType
						),
						$origin
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
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				$origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, FunctionValue $parameter): Value {
			$result = [];
			foreach ($target->values as $value) {
				$r = $parameter->execute($value);
				if ($r instanceof ErrorValue) {
					return $r;
				}
				/** @var TupleValue $r */
				$result = array_merge($result, $r->values);
			}
			return $this->valueRegistry->tuple($result);
		};
	}

}
