<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationType as EnumerationTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<EnumerationSubsetType|MetaType, NullType, NullValue> */
final readonly class EnumerationType extends TypeNativeMethod {

	protected function isTargetRefTypeValid(Type $targetRefType, mixed $origin): bool {
		return $targetRefType instanceof EnumerationSubsetType ||
			($targetRefType instanceof MetaType && (
				$targetRefType->value === MetaTypeValue::EnumerationSubset ||
				$targetRefType->value === MetaTypeValue::Enumeration
			));
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, NullType $parameterType): TypeType {
			$refType = $targetType->refType;
			if ($refType instanceof EnumerationSubsetType) {
				return $this->typeRegistry->type($refType->enumeration);
			}
			return $this->typeRegistry->type($this->typeRegistry->metaType(MetaTypeValue::Enumeration));
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, NullValue $parameter): TypeValue {
			/** @var EnumerationSubsetType|EnumerationTypeInterface $typeValue */
			$typeValue = $this->toBaseType($target->typeValue);
			return $this->valueRegistry->type($typeValue->enumeration);
		};
	}

}
