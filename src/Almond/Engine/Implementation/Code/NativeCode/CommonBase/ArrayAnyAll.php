<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<AnyType, FunctionType, FunctionValue> */
abstract readonly class ArrayAnyAll extends ArrayNativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType, mixed $origin): null|string|ValidationFailure {
		/** @var ArrayType $targetType */
		/** @var FunctionType $parameterType */
		if (!$parameterType->returnType->isSubtypeOf($this->typeRegistry->boolean)) {
			return sprintf(
				"The parameter return type %s must be a subtype of Boolean",
				$parameterType->returnType
			);
		}
		if (!$targetType->itemType->isSubtypeOf($parameterType->parameterType)) {
			return sprintf(
				"The parameter type %s of the callback function must be a supertype of %s",
				$parameterType->parameterType,
				$targetType->itemType
			);
		}
		return null;
	}

	protected function getValidator(): callable {
		return fn(ArrayType $targetType, FunctionType $parameterType, mixed $origin): BooleanType =>
			$this->typeRegistry->boolean;
	}

}