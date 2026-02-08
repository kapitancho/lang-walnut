<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\NamedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;

/**
 * @template TTargetType of NamedType
 * @template TParameterType of Type
 * @template TTargetValue of Value
 * @template TParameterValue of Value
 * @extends NativeMethod<TTargetType, TParameterType, TTargetValue, TParameterValue>
 */
abstract readonly class NamedNativeMethod extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool {
		if (!parent::isTargetTypeValid($targetType, $validator, $origin)) {
			return false;
		}
		/** @var NamedType $targetType */
		return $this->isNamedTypeValid($targetType, $origin);
	}

	protected function isNamedTypeValid(NamedType $namedType, mixed $origin): bool {
		return true;
	}

	protected function isTargetValueValid(Value $target, callable $executor): bool {
		if (!parent::isTargetValueValid($target, $executor)) {
			return false;
		}
		/** @var NamedType $targetType */
		$targetType = $target->type;
		return $this->isNamedTypeValid($targetType, null);
	}

}