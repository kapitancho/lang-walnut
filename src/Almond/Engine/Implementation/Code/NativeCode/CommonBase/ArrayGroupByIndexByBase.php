<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

abstract readonly class ArrayGroupByIndexByBase extends ArrayCallbackBase {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		if ($error = parent::validateParameterType($parameterType, $targetType)) {
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