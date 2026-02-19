<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;

/**
 * @template TItemType of Type
 * @template TParameterType of Type
 * @template TParameterValue of Value
 * @extends NativeMethod<MapType, TParameterType, RecordValue, TParameterValue>
 */
abstract readonly class SetNativeMethod extends NativeMethod {

	protected function validateTargetType(Type $targetType, mixed $origin): null|string {
		if ($targetType instanceof SetType) {
			$itemType = $this->toBaseType($targetType->itemType);
			$expectedType = $this->getExpectedSetItemType();
			if (is_array($expectedType)) {
				foreach ($expectedType as $item) {
					if ($itemType->isSubtypeOf($item)) {
						return null;
					}
				}
				return sprintf("The item type of the target set must be a subtype of one of %s, got %s",
					implode(", ", $expectedType),
					$targetType->itemType
				);
			} else {
				if (!$itemType->isSubtypeOf($expectedType)) {
					return sprintf("The item type of the target set must be a subtype of %s, got %s",
						$expectedType,
						$targetType->itemType
					);

				}
			}
		}
		return null;
	}

	/** @return Type|list<Type> */
	protected function getExpectedSetItemType(): Type|array {
		return $this->typeRegistry->any;
	}

}
