<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\SetNativeMethod;

/** @extends SetNativeMethod<AnyType, SetType, SetValue> */
final readonly class BinaryBitwiseAnd extends SetNativeMethod {

	protected function getValidator(): callable {
		return fn(SetType $targetType, SetType $parameterType): SetType =>
			$this->typeRegistry->set(
				$t = $this->typeRegistry->intersection([
					$targetType->itemType,
					$parameterType->itemType
				]),
				0,
				$t instanceof NothingType ? 0 :
					min($targetType->range->maxLength, $parameterType->range->maxLength),
		);
	}

	protected function getExecutor(): callable {
		return function(SetValue $target, SetValue $parameter): Value {
			$result = [];
			foreach($target->valueSet as $key => $value) {
				if (array_key_exists($key, $parameter->valueSet)) {
					$result[] = $value;
				}
			}
			return $this->valueRegistry->set($result);
		};
	}

}
