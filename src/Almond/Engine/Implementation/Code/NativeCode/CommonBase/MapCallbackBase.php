<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MapNativeMethod;

/** @extends MapNativeMethod<Type, FunctionType, FunctionValue> */
abstract readonly class MapCallbackBase extends MapNativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		if ($targetType instanceof RecordType) {
			$targetType = $targetType->asMapType();
		}
		/** @var MapType $targetType */
		/** @var FunctionType $parameterType */
		if (!$targetType->itemType->isSubtypeOf($parameterType->parameterType)) {
			return sprintf(
				"The item type %s is not a subtype of the of the callback function parameter type %s",
				$targetType->itemType,
				$parameterType->parameterType
			);
		}
		return null;
	}

}