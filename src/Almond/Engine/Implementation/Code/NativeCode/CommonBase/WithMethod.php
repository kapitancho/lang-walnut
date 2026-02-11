<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalKeyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\DataValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\OpenValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\OptionalKeyType as OptionalKeyTypeImpl;

abstract readonly class WithMethod extends NativeMethod {

	/** @param callable(): (Type|ValidationFailure) $typeAdjustFn */
	protected function validateDataOpenType(
		DataType|OpenType $targetType,
		Type $parameterType,
		callable $typeAdjustFn,
		mixed $origin
	): Type|ValidationFailure {
		$valueType = $this->toBaseType($targetType->valueType);
		$pType = $this->toBaseType($parameterType);

		if ($valueType instanceof ArrayType) {
			$pType = $pType instanceof TupleType ? $pType->asArrayType() : $pType;
			if ($pType instanceof ArrayType) {
				if (!$pType->itemType->isSubtypeOf($valueType->itemType)) {
					return $this->validationFactory->error(
						ValidationErrorType::invalidParameterType,
						sprintf("Cannot call 'with' on Array type %s with a parameter of Array type %s due to incompatible item type",
							$targetType, $parameterType),
						$origin
					);
				}
				if (
					$valueType->range->maxLength === PlusInfinity::value || (
						$pType->range->maxLength !== PlusInfinity::value &&
						$pType->range->maxLength <= $valueType->range->maxLength
					)
				) {
					return $typeAdjustFn();
				}
				return $this->validationFactory->error(
					ValidationErrorType::invalidParameterType,
					sprintf("Cannot call 'with' on Array type %s with a parameter of Array type %s due to incompatible length",
						$targetType, $parameterType),
					$origin
				);
			}
		}
		if ($valueType instanceof TupleType) {
			if ($pType instanceof ArrayType) {
				if (
					$pType->range->maxLength === PlusInfinity::value ||
					$pType->range->maxLength > count($valueType->types)
				) {
					return $this->validationFactory->error(
						ValidationErrorType::invalidParameterType,
						sprintf("Cannot call 'with' on Tuple type %s with a parameter of Array type %s due to incompatible length",
							$targetType, $parameterType),
						$origin
					);
				}
				foreach ($valueType->types as $vIndex => $vType) {
					if (!$pType->itemType->isSubtypeOf($vType)) {
						return $this->validationFactory->error(
							ValidationErrorType::invalidParameterType,
							sprintf("Cannot call 'with' on Tuple type %s with a parameter of Array type %s due to incompatible type at index %d",
								$targetType, $parameterType, $vIndex),
							$origin
						);
					}
				}
				return $typeAdjustFn();
			}
			if ($pType instanceof TupleType) {
				if (count($pType->types) > count($valueType->types)) {
					return $this->validationFactory->error(
						ValidationErrorType::invalidParameterType,
						sprintf("Cannot call 'with' on Tuple type %s with a parameter of Tuple type %s due to incompatible length",
							$targetType, $parameterType),
						$origin
					);
				}
				if (!$pType->restType instanceof NothingType) {
					return $this->validationFactory->error(
						ValidationErrorType::invalidParameterType,
						sprintf("Cannot call 'with' on Tuple type %s with a parameter of Tuple type %s with a rest type",
							$targetType, $parameterType),
						$origin
					);
				}
				foreach ($valueType->types as $vIndex => $vType) {
					$pTypeType = $pType->types[$vIndex] ?? $pType->restType;
					if (!$pTypeType->isSubtypeOf($vType)) {
						return $this->validationFactory->error(
							ValidationErrorType::invalidParameterType,
							sprintf("Cannot call 'with' on Tuple type %s with a parameter of Tuple type %s due to incompatible type at index %d",
								$targetType, $parameterType, $vIndex),
							$origin
						);
					}
				}
				return $typeAdjustFn();
			}
		}
		if ($valueType instanceof MapType) {
			$pType = $pType instanceof RecordType ? $pType->asMapType() : $pType;
			if ($pType instanceof MapType) {
				if (!$pType->keyType->isSubtypeOf($valueType->keyType)) {
					return $this->validationFactory->error(
						ValidationErrorType::invalidParameterType,
						sprintf("Cannot call 'with' on Map type %s with a parameter of Map type %s due to incompatible key type",
							$targetType, $parameterType),
						$origin
					);
				}
				if (!$pType->itemType->isSubtypeOf($valueType->itemType)) {
					return $this->validationFactory->error(
						ValidationErrorType::invalidParameterType,
						sprintf("Cannot call 'with' on Map type %s with a parameter of Map type %s due to incompatible item type",
							$targetType, $parameterType),
						$origin
					);
				}
				if ($valueType->range->maxLength === PlusInfinity::value) {
					return $typeAdjustFn();
				}
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("Cannot call 'with' on Map type %s with a limited length", $targetType),
				$origin
			);
		}
		if ($valueType instanceof RecordType) {
			if ($pType instanceof MapType) {
				return $this->validationFactory->error(
					ValidationErrorType::invalidParameterType,
					sprintf("Cannot call 'with' on Record type %s with a parameter of Map type %s",
						$targetType, $parameterType),
					$origin
				);
			}
			if ($pType instanceof RecordType) {
				foreach ($pType->types as $vKey => $vType) {
					$pPropertyType = $valueType->types[$vKey] ?? $valueType->restType;
					if (!$vType->isSubtypeOf($pPropertyType)) {
						return $this->validationFactory->error(
							ValidationErrorType::invalidParameterType,
							sprintf("Cannot call 'with' on Record type %s with a parameter of Record type %s due to incompatible type at key %s",
								$targetType, $parameterType, $vKey),
							$origin
						);
					}
				}
				return $typeAdjustFn();
			}
		}
		// @codeCoverageIgnoreStart
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("[%s] Invalid target type: %s", static::class, $targetType),
			$origin
		);
		// @codeCoverageIgnoreEnd
	}

	protected function getCombinedRecordType(
		RecordType $targetType,
		RecordType $parameterType
	): RecordType {
		$recTypes = [];
		foreach ($targetType->types as $tKey => $tType) {
			$pType = $parameterType->types[$tKey] ?? null;
			if ($tType instanceof OptionalKeyType) {
				$recTypes[$tKey] = $pType instanceof OptionalKeyType ?
					new OptionalKeyTypeImpl(
						$this->typeRegistry->union([
							$tType->valueType,
							$pType->valueType
						])
					) : $pType ?? new OptionalKeyTypeImpl(
						$this->typeRegistry->union([
							$tType->valueType,
							$parameterType->restType
						])
					);
			} else {
				$recTypes[$tKey] = $pType instanceof OptionalKeyType ?
					$this->typeRegistry->union([
						$tType,
						$pType->valueType
					]) : $pType ?? $this->typeRegistry->union([
						$tType,
						$parameterType->restType
					]);
			}
		}
		foreach ($parameterType->types as $pKey => $pType) {
			$recTypes[$pKey] ??= $pType;
		}
		return $this->typeRegistry->record($recTypes,
			$this->typeRegistry->union([
				$targetType->restType,
				$parameterType->restType
			])
		);
	}

	protected function getCombinedMapType(
		MapType $targetType,
		MapType $parameterType
	): MapType {
		return $this->typeRegistry->map(
			$this->typeRegistry->union([
				$targetType->itemType,
				$parameterType->itemType
			]),
			max($targetType->range->minLength, $parameterType->range->minLength),
			$targetType->range->maxLength === PlusInfinity::value ||
				$parameterType->range->maxLength === PlusInfinity::value ? PlusInfinity::value :
				((int)(string)$targetType->range->maxLength) + ((int)(string)$parameterType->range->maxLength),
			$this->typeRegistry->union([
				$targetType->keyType,
				$parameterType->keyType
			]),
		);
	}

	protected function executeDataOpenType(
		DataValue|OpenValue $target,
		Value $parameter,
		callable $constructor
	): Value {
		$baseValue = $target->value;
		if ($baseValue instanceof TupleValue) {
			return $constructor(
				$this->executeArrayItem($baseValue, $parameter)
			);
		}
		if ($baseValue instanceof RecordValue) {
			return $constructor(
				$this->executeMapItem($baseValue, $parameter)
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

	protected function executeArrayItem(
		TupleValue $target,
		Value $parameter
	): Value {
		if ($parameter instanceof TupleValue) {
			$values = $target->values;
			foreach ($parameter->values as $index => $value) {
				$values[$index] = $value;
			}
			return $this->valueRegistry->tuple($values);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

	protected function executeMapItem(
		RecordValue $target,
		RecordValue $parameter
	): RecordValue {
		return $this->valueRegistry->record([
			... $target->values, ... $parameter->values
		]);
	}

}
