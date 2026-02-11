<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<AtomType|MetaType, NullType, NullValue> */
final readonly class AtomValue extends TypeNativeMethod {

	protected function isTargetRefTypeValid(Type $targetRefType, mixed $origin): bool {
		$refType = $this->toBaseType($targetRefType);
		return $refType instanceof AtomType ||
			($refType instanceof MetaType && $refType->value === MetaTypeValue::Atom);
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, NullType $parameterType): Type {
			/** @var AtomType|MetaType $refType */
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof MetaType) {
				return $this->typeRegistry->array($this->typeRegistry->any, 1, 1);
			}
			return $refType;
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, NullValue $parameter): Value {
			/** @var AtomType $refType */
			$refType = $this->toBaseType($target->typeValue);
			return $refType->value;
		};
	}

}
