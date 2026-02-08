<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\SetNativeMethod;

/** @extends SetNativeMethod<AnyType, SetType, SetValue> */
final readonly class BinaryPlus extends SetNativeMethod {

	protected function getValidator(): callable {
		return fn(SetType $targetType, SetType $parameterType): SetType =>
			$this->typeRegistry->set(
				$this->typeRegistry->union([
					$targetType->itemType,
					$parameterType->itemType
				]),
				$targetType->range->minLength + $parameterType->range->minLength,
				$parameterType->range->maxLength === PlusInfinity::value ||
				$targetType->range->maxLength === PlusInfinity::value ?
					PlusInfinity::value : $targetType->range->maxLength + $parameterType->range->maxLength
			);
	}

	protected function getExecutor(): callable {
		return fn(SetValue $target, SetValue $parameter): Value =>
			$this->valueRegistry->set(
				array_values($target->valueSet + $parameter->valueSet)
			);
	}

}
