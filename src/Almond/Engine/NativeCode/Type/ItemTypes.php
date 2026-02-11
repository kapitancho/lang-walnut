<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntersectionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\UnionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<TupleType|RecordType|MetaType, NullType, NullValue> */
final readonly class ItemTypes extends TypeNativeMethod {

	public function validate(Type $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
		$baseTargetType = $this->toBaseType($targetType);
		if ($baseTargetType instanceof IntersectionType) {
			foreach ($baseTargetType->types as $type) {
				$result = $this->validate($type, $parameterType, $origin);
				if ($result instanceof ValidationSuccess) {
					return $result;
				}
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidTargetType,
				sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
				origin: $origin
			);
		}
		return parent::validate($targetType, $parameterType, $origin);
	}

	protected function isTargetRefTypeValid(Type $targetRefType, mixed $origin): bool {
		$refType = $this->toBaseType($targetRefType);
		return $refType instanceof TupleType ||
			$refType instanceof RecordType ||
			($refType instanceof MetaType && in_array($refType->value, [
				MetaTypeValue::Tuple, MetaTypeValue::Record,
				MetaTypeValue::Union, MetaTypeValue::Intersection
			], true));
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, NullType $parameterType): Type {
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof TupleType) {
				return $this->typeRegistry->tuple(
					array_map(
						fn(Type $type) => $this->typeRegistry->type($type),
						$refType->types,
					),
					null
				);
			}
			if ($refType instanceof RecordType) {
				return $this->typeRegistry->record(
					array_map(
						fn(Type $type) => $this->typeRegistry->type($type),
						$refType->types,
					),
					null
				);
			}
			/** @var MetaType $refType */
			if ($refType->value === MetaTypeValue::Record) {
				return $this->typeRegistry->map(
					$this->typeRegistry->type(
						$this->typeRegistry->any,
					),
					0,
					PlusInfinity::value,
					$this->typeRegistry->string()
				);
			}
			return $this->typeRegistry->array(
				$this->typeRegistry->type(
					$this->typeRegistry->any
				)
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, NullValue $parameter): Value {
			$typeValue = $this->toBaseType($target->typeValue);
			if ($typeValue instanceof TupleType || $typeValue instanceof UnionType || $typeValue instanceof IntersectionType) {
				return $this->valueRegistry->tuple(
					array_map(
						fn(Type $type) => $this->valueRegistry->type($type),
						$typeValue->types
					)
				);
			}
			/** @var RecordType $typeValue */
			return $this->valueRegistry->record(
				array_map(
					fn(Type $type) => $this->valueRegistry->type($type),
					$typeValue->types
				)
			);
		};
	}
}
