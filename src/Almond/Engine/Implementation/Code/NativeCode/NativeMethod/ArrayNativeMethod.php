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

	public function validate2(Type $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
		$baseTargetType = $this->toBaseType($targetType);
		if ($baseTargetType instanceof TupleType) {
			$refinedType = $this->getTupleRefinedType($baseTargetType, $parameterType);
			if ($refinedType) {
				return $this->validationFactory->validationSuccess($refinedType);
			}
			$targetType = $baseTargetType->asArrayType();
		}
		return parent::validate($targetType, $parameterType, $origin);
	}

	protected function validateTargetType(Type $targetType, mixed $origin): null|string|ValidationFailure {
		if ($targetType instanceof ArrayType) {
			return $this->validateTargetArrayItemType($targetType->itemType, $origin);
		}
		return null;
	}

	protected function validateTargetArrayItemType(Type $itemType, mixed $origin): null|string {
		return null;
	}

	protected function checkValidatorTargetType(Type $targetType, callable $validator): bool|Type {
		$base = parent::checkValidatorTargetType($targetType, $validator);
		if (!$base && $targetType instanceof TupleType) {
			$arrayType = $targetType->asArrayType();
			$aBase = parent::checkValidatorTargetType($arrayType, $validator);
			if ($aBase) {
				return $aBase === true ? $arrayType : $aBase;
			}
		}
		return $base;
	}

	protected function isTargetTypeValid(Type $targetType, callable $validator): bool|string {
		$targetType = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		if (!parent::isTargetTypeValid($targetType, $validator)) {
			return false;
		}
		/** @var ArrayType $targetType */
		return $this->isTargetItemTypeValid(
			$this->toBaseType($targetType->itemType),
		);
	}

	protected function isTargetItemTypeValid(Type $targetItemType): bool|string {
		return true;
	}

}