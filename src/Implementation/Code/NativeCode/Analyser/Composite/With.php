<?php

namespace Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\DataType;
use Walnut\Lang\Blueprint\Type\IntersectionType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\OptionalKeyType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\DataValue;
use Walnut\Lang\Blueprint\Value\OpenValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

trait With {
	use BaseType;

	/** @param callable(): Type $typeAdjustFn */
	private function analyseDataOpenType(
		Type $targetType,
		Type $parameterType,
		callable $typeAdjustFn
	): Type {
		$type = $this->toBaseType($targetType);
		if ($type instanceof DataType || $type instanceof OpenType) {
			$valueType = $this->toBaseType($type->valueType);

			//TODO - refactor if possible
			$pType = $this->toBaseType($parameterType);
			if ($valueType instanceof ArrayType) {
				$pType = $pType instanceof TupleType ? $pType->asArrayType() : $pType;
				if ($pType instanceof ArrayType) {
					if (!$pType->itemType->isSubtypeOf($valueType->itemType)) {
						throw new AnalyserException(
							sprintf("Cannot call 'with' on Array type %s with a parameter of Array type %s due to incompatible item type",
								$targetType, $parameterType)
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
					throw new AnalyserException(
						sprintf("Cannot call 'with' on Array type %s with a parameter of Array type %s due to incompatible length",
							$targetType, $parameterType)
					);
				}
			}
			if ($valueType instanceof TupleType) {
				if ($pType instanceof ArrayType) {
					if (
						$pType->range->maxLength === PlusInfinity::value ||
						$pType->range->maxLength > count($valueType->types)
					) {
						throw new AnalyserException(
							sprintf("Cannot call 'with' on Tuple type %s with a parameter of Array type %s due to incompatible length",
								$targetType, $parameterType)
						);
					}
					foreach ($valueType->types as $vIndex => $vType) {
						if (!$pType->itemType->isSubtypeOf($vType)) {
							throw new AnalyserException(
								sprintf("Cannot call 'with' on Tuple type %s with a parameter of Array type %s due to incompatible type at index %d",
									$targetType, $parameterType, $vIndex));
						}
					}
					return $typeAdjustFn();
				}
				if ($pType instanceof TupleType) {
					if (count($pType->types) > count($valueType->types)) {
						throw new AnalyserException(
							sprintf("Cannot call 'with' on Tuple type %s with a parameter of Tuple type %s due to incompatible length",
								$targetType, $parameterType)
						);
					}
					if (!$pType->restType instanceof NothingType) {
						throw new AnalyserException(
							sprintf("Cannot call 'with' on Tuple type %s with a parameter of Tuple type %s with a rest type",
								$targetType, $parameterType)
						);
					}
					foreach ($valueType->types as $vIndex => $vType) {
						$pTypeType = $pType->types[$vIndex] ?? $pType->restType;
						if (!$pTypeType->isSubtypeOf($vType)) {
							throw new AnalyserException(
								sprintf("Cannot call 'with' on Tuple type %s with a parameter of Tuple type %s due to incompatible type at index %d",
									$targetType, $parameterType, $vIndex));
						}
					}
					return $typeAdjustFn();
				}
			}
			if ($valueType instanceof MapType) {
				$pType = $pType instanceof RecordType ? $pType->asMapType() : $pType;
				if ($pType instanceof MapType) {
					if (!$pType->keyType->isSubtypeOf($valueType->keyType)) {
						throw new AnalyserException(
							sprintf("Cannot call 'with' on Map type %s with a parameter of Map type %s due to incompatible key type",
								$targetType, $parameterType)
						);
					}
					if (!$pType->itemType->isSubtypeOf($valueType->itemType)) {
						throw new AnalyserException(
							sprintf("Cannot call 'with' on Map type %s with a parameter of Map type %s due to incompatible item type",
								$targetType, $parameterType)
						);
					}
					if (
						$valueType->range->maxLength === PlusInfinity::value
					) {
						return $typeAdjustFn();
					}
				}
				throw new AnalyserException(
					sprintf("Cannot call 'with' on Map type %s with a limited length", $targetType)
				);
			}
			if ($valueType instanceof RecordType) {
				if ($pType instanceof MapType) {
					throw new AnalyserException(
						sprintf("Cannot call 'with' on Record type %s with a parameter of Map type %s",
							$targetType, $parameterType));
				}
				if ($pType instanceof RecordType) {
					foreach ($pType->types as $vKey => $vType) {
						$pPropertyType = $valueType->types[$vKey] ?? $valueType->restType;
						if (!$vType->isSubtypeOf($pPropertyType)) {
							throw new AnalyserException(
								sprintf("Cannot call 'with' on Record type %s with a parameter of Record type %s due to incompatible type at key %s",
									$targetType, $parameterType, $vKey));
						}
					}
					return $typeAdjustFn();
				}
			}
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	private function getCombinedRecordType(
		TypeRegistry $typeRegistry,
		RecordType $targetType,
		RecordType $parameterType
	): RecordType {
		$recTypes = [];
		foreach($targetType->types as $tKey => $tType) {
			$pType = $parameterType->types[$tKey] ?? null;
			if ($tType instanceof OptionalKeyType) {
				$recTypes[$tKey] = $pType instanceof OptionalKeyType ?
					$typeRegistry->optionalKey(
						$typeRegistry->union([
							$tType->valueType,
							$pType->valueType
						])
					) : $pType ?? $typeRegistry->optionalKey(
						$typeRegistry->union([
							$tType->valueType,
							$parameterType->restType
						])
					);
			} else {
				$recTypes[$tKey] = $pType instanceof OptionalKeyType ?
					$typeRegistry->union([
						$tType,
						$pType->valueType
					]): $pType ?? $typeRegistry->union([
					$tType,
					$parameterType->restType
				]);
			}
		}
		foreach ($parameterType->types as $pKey => $pType) {
			$recTypes[$pKey] ??= $pType;
		}
		return $typeRegistry->record($recTypes,
			$typeRegistry->union([
				$targetType->restType,
				$parameterType->restType
			])
		);
	}

	private function getCombinedMapType(
		TypeRegistry $typeRegistry,
		MapType $targetType,
		MapType $parameterType
	): MapType {
		return $typeRegistry->map(
			$typeRegistry->union([
				$targetType->itemType,
				$parameterType->itemType
			]),
			max($targetType->range->minLength, $parameterType->range->minLength),
			$targetType->range->maxLength === PlusInfinity::value ||
				$parameterType->range->maxLength === PlusInfinity::value ? PlusInfinity::value :
				((int)(string)$targetType->range->maxLength) + ((int)(string)$parameterType->range->maxLength),
			$typeRegistry->union([
				$targetType->keyType,
				$parameterType->keyType
			]),
		);
	}

	private function executeDataOpenType(
		ProgramRegistry $programRegistry,
		DataValue|OpenValue $target,
		Value $parameter,
		callable $constructor
	): Value {
		$baseValue = $target->value;

		if ($baseValue instanceof TupleValue) {
			return $constructor(
				$this->executeArrayItem(
					$programRegistry,
					$baseValue,
					$parameter
				)
			);
		}
		if ($baseValue instanceof RecordValue) {
			return $constructor(
				$this->executeMapItem(
					$programRegistry,
					$baseValue,
					$parameter
				)
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

	private function executeArrayItem(
		ProgramRegistry $programRegistry,
		TupleValue $target,
		Value $parameter
	): Value {
		if ($parameter instanceof TupleValue) {
			$values = $target->values;
			foreach ($parameter->values as $index => $value) {
				$values[$index] = $value;
			}
			return $programRegistry->valueRegistry->tuple($values);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

	private function executeMapItem(
		ProgramRegistry $programRegistry,
		RecordValue $target,
		Value $parameter
	): Value {
		if ($parameter instanceof RecordValue) {
			return $programRegistry->valueRegistry->record([
				... $target->values, ... $parameter->values
			]);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}