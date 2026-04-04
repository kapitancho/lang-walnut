<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<MutableType|MetaType|OptionalType, TypeType, TypeValue> */
final readonly class WithValueType extends TypeNativeMethod {

	protected function validateTargetRefType(Type $targetRefType): null|string {
		return $targetRefType instanceof MutableType || $targetRefType instanceof OptionalType ||
			($targetRefType instanceof MetaType && $targetRefType->value === MetaTypeValue::MutableValue) ?
				null :
				sprintf("Target ref type must be a Mutable type or an Optional type, got: %s", $targetRefType);
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, TypeType $parameterType): TypeType {
			/** @var MutableType|MetaType|OptionalType $refType */
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof MutableType || (
				$refType instanceof MetaType && $refType->value === MetaTypeValue::MutableValue
			)) {
				return $this->typeRegistry->type(
					$this->typeRegistry->metaType(MetaTypeValue::MutableValue)
				);
			}
			return $this->typeRegistry->type(
				$this->typeRegistry->optional($parameterType->refType)
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, TypeValue $parameter): TypeValue {
			/** @var MutableType|MetaType|OptionalType $typeValue */
			$typeValue = $this->toBaseType($target->typeValue);
			if ($typeValue instanceof MutableType || (
				$typeValue instanceof MetaType && $typeValue->value === MetaTypeValue::MutableValue
			)) {
				$result = $this->typeRegistry->mutable(
					$parameter->typeValue,
				);
				return $this->valueRegistry->type($result);
			}
			$result = $this->typeRegistry->optional($parameter->typeValue);
			return $this->valueRegistry->type($result);
		};
	}

}
