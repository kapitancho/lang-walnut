<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
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

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool {
		$targetType = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		if (!parent::isTargetTypeValid($targetType, $validator, $origin)) {
			return false;
		}
		/** @var MutableType $targetType */
		return $this->isTargetValueTypeValid(
			$this->toBaseType($targetType->valueType),
			$origin
		);
	}

	protected function isTargetValueTypeValid(Type $targetValueType, mixed $origin): bool {
		return true;
	}

	public function isTargetValueValid(Value $target, callable $executor): bool {
		if (!parent::isTargetValueValid($target, $executor)) {
			return false;
		}
		/** @var MutableValue $target */
		return $this->isTargetValueTypeValid($target->targetType, null);
	}

}