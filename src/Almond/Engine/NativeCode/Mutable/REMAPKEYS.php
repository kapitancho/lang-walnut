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
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MutableNativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends MutableNativeMethod<MapType, FunctionType, FunctionValue> */
final readonly class REMAPKEYS extends MutableNativeMethod {

	protected function validateTargetValueType(Type $valueType): null|string {
		if ($valueType instanceof MapType) {
			return $valueType->range->minLength > 1 ?
				sprintf("REMAPKEYS can only be used on maps with a minimum size of 0 or 1, got %s",
					$valueType
				) : null;
		}
		return sprintf("The value type of the target set must be a Map type, got %s",
			$valueType
		);
	}

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		/** @var MutableType $targetType */
		/** @var MapType $valueType */
		$valueType = $this->toBaseType($targetType->valueType);
		if ($valueType->keyType->isSubtypeOf($parameterType->parameterType)) {
			$r = $parameterType->returnType;
			$returnType = $r instanceof ResultType ? $r->returnType : $r;
			if ($returnType->isSubtypeOf($valueType->keyType)) {
				return null;
			}
			return sprintf(
				"The return type %s of the callback function is not a subtype of %s",
				$returnType,
				$valueType->keyType
			);
		}
		return sprintf(
			"The parameter type %s of the callback function is not a supertype of %s",
			$parameterType->parameterType,
			$valueType->keyType,
		);
	}

	protected function getValidator(): callable {
		return fn(MutableType $targetType, FunctionType $parameterType, mixed $origin): MutableType => $targetType;
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
