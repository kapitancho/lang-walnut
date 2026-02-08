<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\SetNativeMethod;

/** @extends SetNativeMethod<AnyType, SetType, SetValue> */
final readonly class BinaryMinus extends SetNativeMethod {

	protected function getValidator(): callable {
		return fn(SetType $targetType, SetType $parameterType): SetType =>
			$this->typeRegistry->set(
				$targetType->itemType,
				$parameterType->range->maxLength === PlusInfinity::value ? 0 :
					max(0, $targetType->range->minLength - $parameterType->range->maxLength),
				$targetType->range->maxLength,
			);
	}

	protected function getExecutor(): callable {
		return function(SetValue $target, SetValue $parameter): Value {
			$result = [];
			foreach($target->valueSet as $key => $value) {
				if (!array_key_exists($key, $parameter->valueSet)) {
					$result[] = $value;
				}
			}
			return $this->valueRegistry->set($result);
		};
	}

}
