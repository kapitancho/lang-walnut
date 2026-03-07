<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalKeyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<OpenType|SealedType|DataType|MutableType|OptionalKeyType|MetaType, NullType, NullValue> */
final readonly class ValueType extends TypeNativeMethod {

	protected function validateTargetRefType(Type $targetRefType): null|string {
		if ($targetRefType instanceof OpenType || $targetRefType instanceof SealedType ||
			$targetRefType instanceof DataType || $targetRefType instanceof MutableType ||
			$targetRefType instanceof OptionalKeyType
		) {
			return null;
		}
		if ($targetRefType instanceof MetaType && in_array($targetRefType->value, [
				MetaTypeValue::Data, MetaTypeValue::Open,
				MetaTypeValue::Sealed, MetaTypeValue::MutableValue
			], true)) {
			return null;
		}
		return sprintf("The type %s does not have a value type", $targetRefType);
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, NullType $parameterType, mixed $origin): TypeType {
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof OpenType || $refType instanceof SealedType ||
				$refType instanceof DataType || $refType instanceof MutableType ||
				$refType instanceof OptionalKeyType
			) {
				return $this->typeRegistry->type($refType->valueType);
			}
			return $this->typeRegistry->type($this->typeRegistry->any);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, NullValue $parameter): TypeValue {
			$typeValue = $this->toBaseType($target->typeValue);
			if ($typeValue instanceof DataType ||
				$typeValue instanceof OpenType ||
				$typeValue instanceof SealedType ||
				$typeValue instanceof MutableType ||
				$typeValue instanceof OptionalKeyType
			) {
				return $this->valueRegistry->type($typeValue->valueType);
			}
			return $this->valueRegistry->type($typeValue);
		};
	}

}
