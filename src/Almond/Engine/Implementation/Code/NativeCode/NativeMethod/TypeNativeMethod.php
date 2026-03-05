<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;

/**
 * @template TRefType of Type
 * @template TParameterType of Type
 * @template TParameterValue of Value
 * @extends NativeMethod<TypeType, TParameterType, TypeValue, TParameterValue>
 */
abstract readonly class TypeNativeMethod extends NativeMethod {

	protected function validateTargetType(Type $targetType, mixed $origin): null|string {
		if ($targetType instanceof TypeType) {
			return $this->validateTargetRefType($targetType->refType);
		}
		return null;
	}

	protected function validateTargetRefType(Type $targetRefType): null|string {
		return null;
	}

	protected function DISABLE_isTargetValueValid(Value $target, callable $executor): bool {
		if (!parent::isTargetValueValid($target, $executor)) {
			return false;
		}
		/** @var TypeValue $target */
		return $this->validateTargetRefType($target->typeValue) === null;
	}

}