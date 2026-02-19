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

	protected function validateTargetType(Type $targetType, mixed $origin): null|string {
		if ($targetType instanceof TupleType) {
			$targetType = $targetType->asArrayType();
		}
		if ($targetType instanceof ArrayType) {
			$itemType = $this->toBaseType($targetType->itemType);
			$expectedType = $this->getExpectedArrayItemType();
			if (is_array($expectedType)) {
				foreach ($expectedType as $item) {
					if ($itemType->isSubtypeOf($item)) {
						return null;
					}
				}
				return sprintf("The item type of the target array must be a subtype of one of %s, got %s",
					implode(", ", $expectedType),
					$targetType->itemType
				);
			} else {
				if (!$itemType->isSubtypeOf($expectedType)) {
					return sprintf("The item type of the target array must be a subtype of %s, got %s",
						$expectedType,
						$targetType->itemType
					);

				}
			}
		}
		return null;
	}

	/** @return Type|list<Type> */
	protected function getExpectedArrayItemType(): Type|array {
		return $this->typeRegistry->any;
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
		return parent::isTargetTypeValid($targetType, $validator);
	}

}