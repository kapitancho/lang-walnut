<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\SetNativeMethod;

/** @extends SetNativeMethod<AnyType, SetType, SetValue> */
final readonly class BinaryMultiply extends SetNativeMethod {

	protected function getValidator(): callable {
		return function(SetType $targetType, SetType $parameterType): SetType {
			$minLength = $targetType->range->minLength * $parameterType->range->minLength;
			$maxLength = $targetType->range->maxLength === PlusInfinity::value ||
				$parameterType->range->maxLength === PlusInfinity::value ? PlusInfinity::value :
				$targetType->range->maxLength * $parameterType->range->maxLength;

			return $this->typeRegistry->set(
				$this->typeRegistry->tuple([
					$targetType->itemType,
					$parameterType->itemType
				], null),
				$minLength,
				$maxLength
			);
		};
	}

	protected function getExecutor(): callable {
		return function(SetValue $target, SetValue $parameter): Value {
			$result = [];
			foreach ($target->valueSet as $targetValue) {
				foreach ($parameter->valueSet as $parameterValue) {
					$result[] = $this->valueRegistry->tuple([$targetValue, $parameterValue]);
				}
			}
			return $this->valueRegistry->set(array_values($result));
		};
	}

}
