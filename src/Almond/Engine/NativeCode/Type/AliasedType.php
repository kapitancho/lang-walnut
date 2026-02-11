<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<AliasType|MetaType, NullType, NullValue> */
final readonly class AliasedType extends TypeNativeMethod {

	protected function isTargetRefTypeValid(Type $targetRefType, mixed $origin): bool {
		return $targetRefType instanceof AliasType ||
			($targetRefType instanceof MetaType && $targetRefType->value === MetaTypeValue::Alias);
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, NullType $parameterType): TypeType {
			$refType = $targetType->refType;
			if ($refType instanceof AliasType) {
				return $this->typeRegistry->type($refType->aliasedType);
			}
			return $this->typeRegistry->type($this->typeRegistry->any);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, NullValue $parameter): TypeValue {
			/** @var AliasType $typeValue */
			$typeValue = $target->typeValue;
			return $this->valueRegistry->type($typeValue->aliasedType);
		};
	}

}
