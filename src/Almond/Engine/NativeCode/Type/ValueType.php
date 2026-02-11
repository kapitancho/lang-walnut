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
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<OpenType|SealedType|DataType|MutableType|OptionalKeyType|MetaType, NullType, NullValue> */
final readonly class ValueType extends TypeNativeMethod {

	protected function getValidator(): callable {
		return function(TypeType $targetType, NullType $parameterType, mixed $origin): TypeType|ValidationFailure {
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof OpenType || $refType instanceof SealedType ||
				$refType instanceof DataType || $refType instanceof MutableType ||
				$refType instanceof OptionalKeyType
			) {
				return $this->typeRegistry->type($refType->valueType);
			}
			if ($refType instanceof MetaType && (
				$refType->value === MetaTypeValue::Data ||
				$refType->value === MetaTypeValue::Open ||
				$refType->value === MetaTypeValue::Sealed ||
				$refType->value === MetaTypeValue::MutableValue
			)) {
				return $this->typeRegistry->type($this->typeRegistry->any);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidTargetType,
				sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
				origin: $origin
			);
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
