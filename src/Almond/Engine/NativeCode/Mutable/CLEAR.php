<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<MutableType, NullType, MutableValue, NullValue> */
final readonly class CLEAR extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		if ($targetType instanceof MutableType) {
			$valueType = $this->toBaseType($targetType->valueType);
			if ($valueType instanceof RecordType) {
				$valueType = $valueType->asMapType();
			}
			if (($valueType instanceof SetType || $valueType instanceof MapType) && (int)(string)$valueType->range->minLength === 0) {
				return true;
			}
		}
		return false;
	}

	protected function getValidator(): callable {
		return fn(MutableType $targetType, NullType $parameterType): MutableType =>
			$targetType;
	}

	protected function getExecutor(): callable {
		return function(MutableValue $target, NullValue $parameter): MutableValue {
			$targetType = $this->toBaseType($target->targetType);
			if ($targetType instanceof SetType && $target->value instanceof SetValue) {
				$target->value = $this->valueRegistry->set([]);
				return $target;
			}
			if ($targetType instanceof RecordType) {
				$targetType = $targetType->asMapType();
			}
			if ($targetType instanceof MapType && $target->value instanceof RecordValue) {
				$target->value = $this->valueRegistry->record([]);
				return $target;
			}
			return $target;
		};
	}

}
