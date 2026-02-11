<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<TupleType|RecordType|MetaType, TypeType, TypeValue> */
final readonly class WithRestType extends TypeNativeMethod {

	protected function isTargetRefTypeValid(Type $targetRefType, mixed $origin): bool {
		$refType = $this->toBaseType($targetRefType);
		return $refType instanceof TupleType || $refType instanceof RecordType ||
			($refType instanceof MetaType && (
				$refType->value === MetaTypeValue::Tuple || $refType->value === MetaTypeValue::Record
			));
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, TypeType $parameterType): TypeType {
			/** @var TupleType|RecordType|MetaType $refType */
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof TupleType || (
				$refType instanceof MetaType && $refType->value === MetaTypeValue::Tuple
			)) {
				return $this->typeRegistry->type(
					$this->typeRegistry->metaType(
						MetaTypeValue::Tuple
					)
				);
			}
			return $this->typeRegistry->type(
				$this->typeRegistry->metaType(
					MetaTypeValue::Record
				)
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, TypeValue $parameter): TypeValue {
			/** @var TupleType|RecordType|MetaType $typeValue */
			$typeValue = $this->toBaseType($target->typeValue);
			if ($typeValue instanceof TupleType || (
				$typeValue instanceof MetaType && $typeValue->value === MetaTypeValue::Tuple
			)) {
				$result = $this->typeRegistry->tuple(
					$typeValue instanceof TupleType ? $typeValue->types : [],
					$parameter->typeValue,
				);
				return $this->valueRegistry->type($result);
			}
			$result = $this->typeRegistry->record(
				$typeValue instanceof RecordType ? $typeValue->types : [],
				$parameter->typeValue,
			);
			return $this->valueRegistry->type($result);
		};
	}

}
