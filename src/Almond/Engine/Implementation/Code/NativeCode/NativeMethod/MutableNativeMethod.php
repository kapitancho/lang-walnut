<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;

/**
 * @template TValueType of Type
 * @template TParameterType of Type
 * @template TParameterValue of Value
 * @extends NativeMethod<MutableType, TParameterType, MutableValue, TParameterValue>
 */
abstract readonly class MutableNativeMethod extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, Expression|null $origin): bool {
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

	protected function isTargetValueTypeValid(Type $targetValueType, Expression|null $origin): bool {
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