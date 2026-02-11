<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<ArrayType|MapType|SetType, NullType, NullValue> */
final readonly class ItemType extends TypeNativeMethod {

	protected function isTargetRefTypeValid(Type $targetRefType, mixed $origin): bool {
		$refType = $this->toBaseType($targetRefType);
		return $refType instanceof ArrayType || $refType instanceof MapType || $refType instanceof SetType;
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, NullType $parameterType): TypeType {
			/** @var ArrayType|MapType|SetType $refType */
			$refType = $this->toBaseType($targetType->refType);
			return $this->typeRegistry->type($refType->itemType);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, NullValue $parameter): TypeValue {
			/** @var ArrayType|MapType|SetType $typeValue */
			$typeValue = $this->toBaseType($target->typeValue);
			return $this->valueRegistry->type($typeValue->itemType);
		};
	}

}
