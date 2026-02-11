<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ShapeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<TypeType|ShapeType, NullType, NullValue> */
final readonly class RefType extends TypeNativeMethod {

	protected function isTargetRefTypeValid(Type $targetRefType, mixed $origin): bool {
		$refType = $this->toBaseType($targetRefType);
		return $refType instanceof TypeType || $refType instanceof ShapeType;
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, NullType $parameterType): TypeType {
			/** @var TypeType|ShapeType $refType */
			$refType = $this->toBaseType($targetType->refType);
			return $this->typeRegistry->type($refType->refType);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, NullValue $parameter): TypeValue {
			/** @var TypeType|ShapeType $typeValue */
			$typeValue = $this->toBaseType($target->typeValue);
			return $this->valueRegistry->type($typeValue->refType);
		};
	}

}
