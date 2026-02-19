<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntersectionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\UnionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<Type, Type, Value> */
final readonly class WithItemTypes extends TypeNativeMethod {

	protected function isTargetRefTypeValid(Type $targetRefType): bool {
		$refType = $this->toBaseType($targetRefType);
		return $refType instanceof TupleType ||
			$refType instanceof RecordType ||
			$refType instanceof IntersectionType ||
			$refType instanceof UnionType ||
			($refType instanceof MetaType && in_array($refType->value, [
				MetaTypeValue::Tuple, MetaTypeValue::Record,
				MetaTypeValue::Intersection, MetaTypeValue::Union
			], true));
	}

	protected function isParameterTypeValid(Type $parameterType, callable $validator, Type $targetType): bool {
		if (!parent::isParameterTypeValid($parameterType, $validator, $targetType)) {
			return false;
		}
		return $parameterType->isSubtypeOf(
			$this->typeRegistry->array(
				$this->typeRegistry->type($this->typeRegistry->any)
			)
		) || $parameterType->isSubtypeOf(
			$this->typeRegistry->map(
				$this->typeRegistry->type($this->typeRegistry->any),
				0,
				PlusInfinity::value,
				$this->typeRegistry->string()
			)
		);
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, Type $parameterType): Type {
			$refType = $this->toBaseType($targetType->refType);
			if ($parameterType->isSubtypeOf(
				$this->typeRegistry->array(
					$this->typeRegistry->type($this->typeRegistry->any)
				)
			)) {
				if ($refType instanceof TupleType || (
					$refType instanceof MetaType && $refType->value === MetaTypeValue::Tuple
				)) {
					return $this->typeRegistry->type(
						$this->typeRegistry->metaType(MetaTypeValue::Tuple)
					);
				}
				if ($refType instanceof IntersectionType || (
					$refType instanceof MetaType && $refType->value === MetaTypeValue::Intersection
				)) {
					return $this->typeRegistry->type(
						$this->typeRegistry->metaType(MetaTypeValue::Intersection)
					);
				}
				/** @var UnionType|MetaType $refType */
				return $this->typeRegistry->type(
					$this->typeRegistry->metaType(MetaTypeValue::Union)
				);
			}
			/** @var RecordType|MetaType $refType */
			return $this->typeRegistry->type(
				$this->typeRegistry->metaType(MetaTypeValue::Record)
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, Value $parameter): Value {
			$typeValue = $this->toBaseType($target->typeValue);
			if ($parameter->type->isSubtypeOf(
				$this->typeRegistry->array(
					$this->typeRegistry->type(
						$this->typeRegistry->any
					)
				)
			)) {
				if ($typeValue instanceof TupleType || (
					$typeValue instanceof MetaType && $typeValue->value === MetaTypeValue::Tuple
				)) {
					$result = $this->typeRegistry->tuple(
						array_map(fn(TypeValue $tv): Type => $tv->typeValue, $parameter->values),
						$typeValue instanceof TupleType ? $typeValue->restType : $this->typeRegistry->nothing,
					);
					return $this->valueRegistry->type($result);
				}
				if ($typeValue instanceof IntersectionType || (
					$typeValue instanceof MetaType && $typeValue->value === MetaTypeValue::Intersection
				)) {
					$result = $this->typeRegistry->intersection(
						array_map(fn(TypeValue $tv): Type => $tv->typeValue, $parameter->values),
					);
					return $this->valueRegistry->type($result);
				}
				if ($typeValue instanceof UnionType || (
					$typeValue instanceof MetaType && $typeValue->value === MetaTypeValue::Union
				)) {
					$result = $this->typeRegistry->union(
						array_map(fn(TypeValue $tv): Type => $tv->typeValue, $parameter->values),
					);
					return $this->valueRegistry->type($result);
				}
			}
			if ($parameter->type->isSubtypeOf(
				$this->typeRegistry->map(
					$this->typeRegistry->type(
						$this->typeRegistry->any
					),
					0,
					PlusInfinity::value,
					$this->typeRegistry->string()
				)
			)) {
				if ($typeValue instanceof RecordType || (
					$typeValue instanceof MetaType && $typeValue->value === MetaTypeValue::Record
				)) {
					$result = $this->typeRegistry->record(
						array_map(fn(TypeValue $tv): Type => $tv->typeValue, $parameter->values),
						$typeValue instanceof RecordType ? $typeValue->restType : $this->typeRegistry->nothing,
					);
					return $this->valueRegistry->type($result);
				}
			}
			// @codeCoverageIgnoreStart
			throw new \Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException("Invalid target value");
			// @codeCoverageIgnoreEnd
		};
	}

}
