<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\SetNativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

/** @extends SetNativeMethod<AnyType, SetType, SetValue> */
final readonly class BinaryBitwiseXor extends SetNativeMethod {

	protected function getValidator(): callable {
		return fn(SetType $targetType, SetType $parameterType): SetType =>
			$this->typeRegistry->set(
				$this->typeRegistry->union([
					$targetType->itemType,
					$parameterType->itemType
				]),
				0,
				$parameterType->range->maxLength === PlusInfinity::value ||
				$targetType->range->maxLength === PlusInfinity::value ?
					PlusInfinity::value : $targetType->range->maxLength + $parameterType->range->maxLength
			);
	}

	protected function getExecutor(): callable {
		return function(SetValue $target, SetValue $parameter): Value {
			$exclude = [];
			foreach($parameter->values as $value) {
				$exclude[(string)$value] = $value;
			}
			$result = [];
			foreach($target->values as $value) {
				if (array_key_exists((string)$value, $exclude)) {
					unset($exclude[(string)$value]);
				} else {
					$result[] = $value;
				}
			}
			foreach ($exclude as $value) {
				$result[] = $value;
			}
			return $this->valueRegistry->set($result);
		};
	}

}
