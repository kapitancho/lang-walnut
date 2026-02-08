<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntersectionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\UnionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type as TypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class WithItemTypes implements NativeMethod {

	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(TypeInterface $targetType, TypeInterface $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType);
			if ($parameterType->isSubtypeOf(
				$this->typeRegistry->array(
					$this->typeRegistry->type(
						$this->typeRegistry->any
					)
				)
			)) {
				if ($refType instanceof TupleType || (
					$refType instanceof MetaType && $refType->value === MetaTypeValue::Tuple
				)) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->type(
							$this->typeRegistry->metaType(
								MetaTypeValue::Tuple
							)
						)
					);
				}
				if ($refType instanceof IntersectionType || (
					$refType instanceof MetaType && $refType->value === MetaTypeValue::Intersection
				)) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->type(
							$this->typeRegistry->metaType(
								MetaTypeValue::Intersection
							)
						)
					);
				}
				if ($refType instanceof UnionType || (
					$refType instanceof MetaType && $refType->value === MetaTypeValue::Union
				)) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->type(
							$this->typeRegistry->metaType(
								MetaTypeValue::Union
							)
						)
					);
				}
				// @codeCoverageIgnoreStart
				return $this->validationFactory->error(
					ValidationErrorType::invalidTargetType,
					sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
					origin: $origin
				);
				// @codeCoverageIgnoreEnd
			}
			if ($parameterType->isSubtypeOf(
				$this->typeRegistry->map(
					$this->typeRegistry->type(
						$this->typeRegistry->any
					),
					0,
					PlusInfinity::value,
					$this->typeRegistry->string()
				)
			)) {
				if ($refType instanceof RecordType || (
					$refType instanceof MetaType && $refType->value === MetaTypeValue::Record
				)) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->type(
							$this->typeRegistry->metaType(
								MetaTypeValue::Record
							)
						)
					);
				}
				// @codeCoverageIgnoreStart
				return $this->validationFactory->error(
					ValidationErrorType::invalidTargetType,
					sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
					origin: $origin
				);
				// @codeCoverageIgnoreEnd
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				origin: $origin
			);
		}
		// @codeCoverageIgnoreStart
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
			origin: $origin
		);
		// @codeCoverageIgnoreEnd
	}

	public function execute(Value $target, Value $parameter): Value {
		if ($target instanceof TypeValue) {
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
						array_map(fn(TypeValue $tv): TypeInterface => $tv->typeValue, $parameter->values),
						$typeValue instanceof TupleType ? $typeValue->restType : $this->typeRegistry->nothing,
					);
					return $this->valueRegistry->type($result);
				}
				if ($typeValue instanceof IntersectionType || (
					$typeValue instanceof MetaType && $typeValue->value === MetaTypeValue::Intersection
				)) {
					$result = $this->typeRegistry->intersection(
						array_map(fn(TypeValue $tv): TypeInterface => $tv->typeValue, $parameter->values),
					);
					return $this->valueRegistry->type($result);
				}
				if ($typeValue instanceof UnionType || (
					$typeValue instanceof MetaType && $typeValue->value === MetaTypeValue::Union
				)) {
					$result = $this->typeRegistry->union(
						array_map(fn(TypeValue $tv): TypeInterface => $tv->typeValue, $parameter->values),
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
						array_map(fn(TypeValue $tv): TypeInterface => $tv->typeValue, $parameter->values),
						$typeValue instanceof RecordType ? $typeValue->restType : $this->typeRegistry->nothing,
					);
					return $this->valueRegistry->type($result);
				}
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}
