<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;

/**
 * @template TValueType of Type
 * @template TParameterType of Type
 * @template TParameterValue of Value
 * @extends NativeMethod<MutableType, TParameterType, MutableValue, TParameterValue>
 */
abstract readonly class MutableNativeMethod extends NativeMethod {

	protected function validateTargetType(Type $targetType, mixed $origin): null|string {
		if ($targetType instanceof MutableType) {
			/** @var MutableType $targetType */
			$valueType = $this->toBaseType($targetType->valueType);
			return $this->validateTargetValueType($valueType);
		}
		return null;
	}

	protected function validateTargetValueType(Type $valueType): null|string {
		return null;
	}

}