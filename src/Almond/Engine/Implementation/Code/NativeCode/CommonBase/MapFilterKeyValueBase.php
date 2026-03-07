<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MapNativeMethod;

/** @extends MapNativeMethod<AnyType, FunctionType, FunctionValue> */
abstract readonly class MapFilterKeyValueBase extends MapNativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		/** @var MapType $targetType */
		/** @var FunctionType $parameterType */

		$kv = $this->typeRegistry->record([
			'key' => $targetType->keyType,
			'value' => $targetType->itemType
		], null);

		if (!$kv->isSubtypeOf($parameterType->parameterType)) {
			return sprintf(
				"The callback function parameter type %s should be a supertype of %s",
				$targetType->itemType,
				$kv,
			);
		}

		$expectedReturnType = $this->typeRegistry->result($this->typeRegistry->boolean, $this->typeRegistry->core->itemNotFound);
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