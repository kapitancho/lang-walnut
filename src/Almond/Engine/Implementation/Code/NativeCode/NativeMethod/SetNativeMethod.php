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

	protected function isTargetTypeValid(Type $targetType, callable $validator): bool {
		if (!parent::isTargetTypeValid($targetType, $validator)) {
			return false;
		}
		/** @var SetType $targetType */
		return $this->isTargetItemTypeValid(
			$this->toBaseType($targetType->itemType),
		);
	}

	protected function isTargetItemTypeValid(Type $targetItemType): bool {
		return true;
	}

}
