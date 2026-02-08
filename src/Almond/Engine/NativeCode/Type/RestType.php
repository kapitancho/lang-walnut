<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<TupleType|RecordType|MetaType, NullType, NullValue> */
final readonly class RestType extends TypeNativeMethod {

	protected function getValidator(): callable {
		return function (TypeType $targetType, NullType $parameterType): TypeType {
			/** @var TupleType|RecordType|MetaType $refType */
			$refType = $this->toBaseType($targetType->refType);
			return $this->typeRegistry->type(
				$refType instanceof TupleType || $refType instanceof RecordType ?
					$refType->restType : $this->typeRegistry->any
			);
		};
	}

	protected function getExecutor(): callable {
		return function (TypeValue $target, NullValue $parameter): TypeValue {
			/** @var TupleType|RecordType $typeValue */
			$typeValue = $this->toBaseType($target->typeValue);
			return $this->valueRegistry->type($typeValue->restType);
		};
	}

	protected function isTargetRefTypeValid(Type $targetRefType, mixed $origin): bool {
		return $targetRefType instanceof TupleType || $targetRefType instanceof RecordType ||
			($targetRefType instanceof MetaType &&
				(
					$targetRefType->value === MetaTypeValue::Tuple ||
					$targetRefType->value === MetaTypeValue::Record
				)
			);
	}
}
