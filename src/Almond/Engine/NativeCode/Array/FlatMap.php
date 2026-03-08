<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, FunctionType, FunctionValue> */
final readonly class FlatMap extends ArrayNativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		/** @var ArrayType $targetType */
		/** @var FunctionType $parameterType */
		if (!$targetType->itemType->isSubtypeOf($parameterType->parameterType)) {
			return sprintf(
				"The parameter type %s of the callback function is not a subtype of %s",
				$targetType->itemType,
				$parameterType->parameterType
			);
		}
		/** @var FunctionType $parameterType */
		$r = $parameterType->returnType;
		$returnType = $r instanceof ResultType ? $r->returnType : $r;

		return $returnType->isSubtypeOf($this->typeRegistry->array()) ?
			null : sprintf(
				"The return type of the callback function must be a subtype of Array, got %s",
				$returnType
			);
	}

	protected function getValidator(): callable {
		return function(ArrayType $targetType, FunctionType $parameterType, mixed $origin): Type {
			$r = $parameterType->returnType;
			$errorType = $r instanceof ResultType ? $r->errorType : null;
			$returnType = $r instanceof ResultType ? $r->returnType : $r;

			/** @var ArrayType $returnType */
			$returnType = $this->toBaseType($returnType);
			$minLength = $targetType->range->minLength * $returnType->range->minLength;
			$maxLength = $targetType->range->maxLength === PlusInfinity::value ||
				$returnType->range->maxLength === PlusInfinity::value ?
				PlusInfinity::value :
				$targetType->range->maxLength * $returnType->range->maxLength;

			$resultArray = $this->typeRegistry->array(
				$returnType->itemType,
				$minLength,
				$maxLength
			);
			return $errorType ? $this->typeRegistry->result($resultArray, $errorType) : $resultArray;
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
