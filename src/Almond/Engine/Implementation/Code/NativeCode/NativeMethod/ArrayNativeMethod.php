<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;

/**
 * @template TItemType of Type
 * @template TParameterType of Type
 * @template TParameterValue of Value
 * @extends NativeMethod<ArrayType, TParameterType, TupleValue, TParameterValue>
 */
abstract readonly class ArrayNativeMethod extends NativeMethod {

	public function validate(Type $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
		$baseTargetType = $this->toBaseType($targetType);
		if ($baseTargetType instanceof TupleType) {
			$targetType = $baseTargetType->asArrayType();
		}
		return parent::validate($targetType, $parameterType, $origin);
	}

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool {
		$targetType = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		if (!parent::isTargetTypeValid($targetType, $validator, $origin)) {
			return false;
		}
		/** @var ArrayType $targetType */
		return $this->isTargetItemTypeValid(
			$this->toBaseType($targetType->itemType),
			$origin
		);
	}

	protected function isTargetItemTypeValid(Type $targetItemType, mixed $origin): bool {
		return true;
	}

}