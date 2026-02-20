<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\NamedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<NamedType|MetaType, NullType, NullValue> */
final readonly class TypeName extends TypeNativeMethod {

	protected function validateTargetRefType(Type $targetRefType): null|string {
		if ($targetRefType instanceof NamedType) {
			return null;
		}
		return $targetRefType instanceof MetaType && in_array($targetRefType->value, [
			MetaTypeValue::Named,
			MetaTypeValue::Atom,
			MetaTypeValue::Enumeration,
			MetaTypeValue::Alias,
			MetaTypeValue::Data,
			MetaTypeValue::Open,
			MetaTypeValue::Sealed,
		], true) ? null : sprintf("Target ref type must be a Named type, got: %s", $targetRefType);
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, NullType $parameterType): Type {
			$refType = $targetType->refType;
			if ($refType instanceof NamedType) {
				return $this->typeRegistry->stringSubset([$refType->name->identifier]);
			}
			return $this->typeRegistry->string(1);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, NullValue $parameter): StringValue {
			/** @var NamedType $typeValue */
			$typeValue = $target->typeValue;
			return $this->valueRegistry->string($typeValue->name->identifier);
		};
	}

}
