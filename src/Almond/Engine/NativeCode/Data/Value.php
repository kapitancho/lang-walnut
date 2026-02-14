<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Data;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\DataValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value as ValueInterface;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<DataType|MetaType, NullType, DataValue, NullValue> */
final readonly class Value extends NativeMethod {

	protected function getValidator(): callable {
		return function(DataType|MetaType $targetType, NullType $parameterType): Type {
			if ($targetType instanceof DataType) {
				return $targetType->valueType;
			}
			return $this->typeRegistry->any;
		};
	}

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		if ($targetType instanceof MetaType && $targetType->value === MetaTypeValue::Data) {
			return true;
		}
		return parent::isTargetTypeValid($targetType, $validator, $origin);
	}

	protected function getExecutor(): callable {
		return fn(DataValue $target, NullValue $parameter): ValueInterface =>
			$target->value;
	}

}
