<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EmptyValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, FunctionType, FunctionValue> */
final readonly class FlipMap extends ArrayNativeMethod {

	protected function getExpectedArrayItemType(): Type {
		return $this->typeRegistry->string();
	}

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		/** @var ArrayType $targetType */
		/** @var FunctionType $parameterType */
		return $targetType->itemType->isSubtypeOf($parameterType->parameterType) ?
			null : sprintf(
				"The parameter type %s of the callback function is not a supertype of %s",
				$parameterType->parameterType,
				$targetType->itemType
			);
	}

	protected function getValidator(): callable {
		return function(ArrayType $targetType, FunctionType $parameterType, mixed $origin): Type {
			$itemType = $targetType->itemType;
			$r = $parameterType->returnType;
			if ($isOptional = $r instanceof OptionalType) {
				$r = $r->valueType;
			}
			$errorType = $r instanceof ResultType ? $r->errorType : null;
			$returnType = $r instanceof ResultType ? $r->returnType : $r;
			$t = $this->typeRegistry->map(
				$returnType,
				$isOptional ? 0 :
					min(1, $targetType->range->minLength),
				$targetType->range->maxLength,
				$itemType
			);
			return $errorType ? $this->typeRegistry->result($t, $errorType) : $t;
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, FunctionValue $parameter): Value {
			$result = [];
			foreach ($target->values as $value) {
				/** @var StringValue $value */
				$r = $parameter->execute($value);
				if ($r instanceof ErrorValue) {
					return $r;
				}
				if ($r instanceof EmptyValue) {
					continue;
				}
				$result[$value->literalValue] = $r;
			}
			return $this->valueRegistry->record($result);
		};
	}

}
