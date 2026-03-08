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
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<Type, Type, Value> */
final readonly class WithItemTypes extends TypeNativeMethod {

	protected function validateTargetRefType(Type $targetRefType): null|string {
		return $targetRefType instanceof TupleType ||
			$targetRefType instanceof RecordType ||
			$targetRefType instanceof IntersectionType ||
			$targetRefType instanceof UnionType ||
			($targetRefType instanceof MetaType && in_array($targetRefType->value, [
				MetaTypeValue::Tuple, MetaTypeValue::Record,
				MetaTypeValue::Intersection, MetaTypeValue::Union
			], true)) ?
				null :
			sprintf("Target ref type must be a Tuple type, a Record type, an Intersection type or a Union type, got: %s", $targetRefType);
	}

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
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
		) ? null : sprintf(
			"The parameter type must be an Array type or a Map type, got: %s",
			$parameterType
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
					/** @var TupleValue $parameter */
					/** @var list<TypeValue> $types */
					$types = $parameter->values;
					$result = $this->typeRegistry->tuple(
						array_map(fn(TypeValue $tv): Type => $tv->typeValue, $types),
						$typeValue instanceof TupleType ? $typeValue->restType : $this->typeRegistry->nothing,
					);
					return $this->valueRegistry->type($result);
				}
				if ($typeValue instanceof IntersectionType || (
					$typeValue instanceof MetaType && $typeValue->value === MetaTypeValue::Intersection
				)) {
					/** @var TupleValue $parameter */
					/** @var list<TypeValue> $types */
					$types = $parameter->values;
					$result = $this->typeRegistry->intersection(
						array_map(fn(TypeValue $tv): Type => $tv->typeValue, $types),
					);
					return $this->valueRegistry->type($result);
				}
				if ($typeValue instanceof UnionType || (
					$typeValue instanceof MetaType && $typeValue->value === MetaTypeValue::Union
				)) {
					/** @var TupleValue $parameter */
					/** @var list<TypeValue> $types */
					$types = $parameter->values;
					$result = $this->typeRegistry->union(
						array_map(fn(TypeValue $tv): Type => $tv->typeValue, $types),
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
					/** @var RecordValue $parameter */
					/** @var array<string, TypeValue> $types */
					$types = $parameter->values;
					$result = $this->typeRegistry->record(
						array_map(fn(TypeValue $tv): Type => $tv->typeValue, $types),
						$typeValue instanceof RecordType ? $typeValue->restType : $this->typeRegistry->nothing,
					);
					return $this->valueRegistry->type($result);
				}
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid target value");
			// @codeCoverageIgnoreEnd
		};
	}

}
