<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;

/**
 * @template TRefType of Type
 * @template TParameterType of Type
 * @template TParameterValue of Value
 * @extends NativeMethod<TypeType, TParameterType, TypeValue, TParameterValue>
 */
abstract readonly class TypeNativeMethod extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, Expression|null $origin): bool {
		if (!parent::isTargetTypeValid($targetType, $validator, $origin)) {
			return false;
		}
		/** @var TypeType $targetType */
		return $this->isTargetRefTypeValid($targetType->refType, $origin);
	}

	protected function isTargetRefTypeValid(Type $targetRefType, Expression|null $origin): bool {
		return true;
	}

	protected function isTargetValueValid(Value $target, callable $executor): bool {
		if (!parent::isTargetValueValid($target, $executor)) {
			return false;
		}
		/** @var TypeValue $target */
		return $this->isTargetRefTypeValid($target->typeValue);
	}

}