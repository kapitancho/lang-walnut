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

	protected function isTargetTypeValid(Type $targetType, callable $validator): bool {
		if (!parent::isTargetTypeValid($targetType, $validator)) {
			return false;
		}
		/** @var TypeType $targetType */
		return $this->isTargetRefTypeValid($targetType->refType);
	}

	protected function isTargetRefTypeValid(Type $targetRefType): bool {
		return true;
	}

	protected function isTargetValueValid(Value $target, callable $executor): bool {
		if (!parent::isTargetValueValid($target, $executor)) {
			return false;
		}
		/** @var TypeValue $target */
		return $this->isTargetRefTypeValid($target->typeValue, null);
	}

}