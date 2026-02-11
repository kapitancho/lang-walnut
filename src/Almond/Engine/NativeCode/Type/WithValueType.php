<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalKeyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\OptionalKeyType as OptionalKeyTypeImpl;

/** @extends TypeNativeMethod<MutableType|MetaType|OptionalKeyType, TypeType, TypeValue> */
final readonly class WithValueType extends TypeNativeMethod {

	protected function isTargetRefTypeValid(Type $targetRefType, mixed $origin): bool {
		$refType = $this->toBaseType($targetRefType);
		return $refType instanceof MutableType || $refType instanceof OptionalKeyType ||
			($refType instanceof MetaType && $refType->value === MetaTypeValue::MutableValue);
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, TypeType $parameterType): TypeType {
			/** @var MutableType|MetaType|OptionalKeyType $refType */
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof MutableType || (
				$refType instanceof MetaType && $refType->value === MetaTypeValue::MutableValue
			)) {
				return $this->typeRegistry->type(
					$this->typeRegistry->metaType(MetaTypeValue::MutableValue)
				);
			}
			return $this->typeRegistry->type(
				new OptionalKeyTypeImpl($parameterType->refType)
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, TypeValue $parameter): TypeValue {
			/** @var MutableType|MetaType|OptionalKeyType $typeValue */
			$typeValue = $this->toBaseType($target->typeValue);
			if ($typeValue instanceof MutableType || (
				$typeValue instanceof MetaType && $typeValue->value === MetaTypeValue::MutableValue
			)) {
				$result = $this->typeRegistry->mutable(
					$parameter->typeValue,
				);
				return $this->valueRegistry->type($result);
			}
			$result = new OptionalKeyTypeImpl(
				$parameter->typeValue,
			);
			return $this->valueRegistry->type($result);
		};
	}

}
