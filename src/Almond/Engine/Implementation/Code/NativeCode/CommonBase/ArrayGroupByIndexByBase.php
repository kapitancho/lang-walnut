<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, FunctionType, FunctionValue> */
abstract readonly class ArrayGroupByIndexByBase extends ArrayCallbackBase {

	protected function validateParameterType(Type $parameterType, Type $targetType, mixed $origin): null|string|ValidationFailure {
		if ($error = parent::validateParameterType($parameterType, $targetType, $origin)) {
			return $error;
		}
		/** @var ArrayType $targetType */
		/** @var FunctionType $parameterType */
		$expectedReturnType = $this->typeRegistry->string();
		if (!$parameterType->returnType->isSubtypeOf($expectedReturnType)) {
			return sprintf(
				"The return type of the callback function must be a subtype of %s, but got %s",
				$expectedReturnType,
				$parameterType->returnType
			);
		}
		return null;
	}

}