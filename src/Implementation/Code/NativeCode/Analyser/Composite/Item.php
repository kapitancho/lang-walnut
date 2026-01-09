<?php

namespace Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite;

use BcMath\Number;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\DataType;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\IntersectionType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\OptionalKeyType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\DataValue;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\OpenValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

trait Item {
	use BaseType;

	private function analyseDataOpenType(
		TypeRegistry $typeRegistry,
		DataType|OpenType $targetType,
		Type $parameterType
	): Type {
		$valueType = $this->toBaseType($targetType->valueType);
		if ($valueType instanceof TupleType || $valueType instanceof ArrayType) {
			return $this->analyseArrayItem($typeRegistry, $valueType, $parameterType);
		}
		if ($valueType instanceof RecordType || $valueType instanceof MapType) {
			return $this->analyseMapItem($typeRegistry, $valueType, $parameterType);
		}
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
	}

	private function analyseArrayItem(
		TypeRegistry $typeRegistry,
		ArrayType|TupleType $targetType,
		Type $parameterType
	): Type {
		if ($parameterType instanceof IntegerType) {
			$arrayType = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
			$returnType = $arrayType->itemType;
			if ($targetType instanceof TupleType) {
				$min = $parameterType->numberRange->min;
				$max = $parameterType->numberRange->max;
				if ($min !== MinusInfinity::value && $min->value >= 0) {
					if ($parameterType instanceof IntegerSubsetType) {
						$returnType = $typeRegistry->union(
							array_map(
								static fn(Number $value) =>
									$targetType->types[(string)$value] ?? $targetType->restType,
								$parameterType->subsetValues
							)
						);
					} else {
						$isWithinLimit = $max !== PlusInfinity::value && $max->value < count($targetType->types);
						$returnType = $typeRegistry->union(
							$isWithinLimit ?
							array_slice($targetType->types, (int)(string)$min->value, (int)(string)$max->value - (int)(string)$min->value + 1) :
							[... array_slice($targetType->types, (int)(string)$min->value), $targetType->restType]
						);
					}
				}
			}

			return $parameterType->numberRange->max !== PlusInfinity::value &&
				$arrayType->range->minLength > $parameterType->numberRange->max->value ?
				$returnType :
				$typeRegistry->result(
					$returnType,
					$typeRegistry->core->indexOutOfRange
				);
		}
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
	}

	private function analyseMapItem(
		TypeRegistry $typeRegistry,
		IntersectionType|MapType|RecordType|MetaType|AliasType $targetType,
		Type $parameterType
	): Type {
		if ($targetType instanceof AliasType) {
			$targetType = $this->toBaseType($targetType);
		}
		if ($targetType instanceof IntersectionType) {
			//TODO: this is not a long-term fix.
			/** @var list<MapType|RecordType|MetaType> $intersectionTypes */
			$intersectionTypes = $targetType->types;
			$types = array_map(
				fn(MapType|RecordType|MetaType|AliasType $type) => $this->analyseMapItem($typeRegistry, $type, $parameterType),
				$intersectionTypes
			);
			return $typeRegistry->intersection($types);
		}
		$mapType = $targetType instanceof RecordType ? $targetType->asMapType() : $targetType;
		if ($targetType instanceof MetaType && $targetType->value === MetaTypeValue::Record) {
			$mapType = $typeRegistry->map($typeRegistry->any);
		}
		$mapItemNotFound = $typeRegistry->core->mapItemNotFound;

		$parameterType = $this->toBaseType($parameterType);
		if ($parameterType instanceof StringType) {
			$returnType = $mapType->itemType;
			if ($targetType instanceof RecordType && $parameterType instanceof StringSubsetType) {
				$tConv = static fn(Type $fType): Type => $fType instanceof OptionalKeyType ?
					$typeRegistry->result($fType->valueType, $mapItemNotFound) :
					$fType;
				$returnType = $typeRegistry->union(
					array_map(
						static fn(string $value) => $tConv(
							$targetType->types[$value] ??
							$targetType->restType
						),
						$parameterType->subsetValues
					)
				);
				$allKeys = array_filter($parameterType->subsetValues,
					static fn(string $value) => array_key_exists($value, $targetType->types)
				);
				if (count($allKeys) === count($parameterType->subsetValues)) {
					return $returnType;
				}
			}
			/*if ($returnType instanceof NothingType) {
				throw new AnalyserException(sprintf("[%s] No property exists that matches the type: %s", __CLASS__, $parameterType));
			}*/
			return $typeRegistry->result($returnType, $mapItemNotFound);
		}
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
	}

	private function executeDataOpenType(
		ProgramRegistry $programRegistry,
		DataValue|OpenValue $target,
		Value $parameter
	): Value {
		$baseValue = $target->value;
		if ($baseValue instanceof TupleValue) {
			return $this->executeArrayItem($programRegistry, $baseValue, $parameter);
		}
		if ($baseValue instanceof RecordValue) {
			return $this->executeMapItem($programRegistry, $baseValue, $parameter);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

	private function executeArrayItem(
		ProgramRegistry $programRegistry,
		TupleValue $target,
		Value $parameter
	): Value {
		if ($parameter instanceof IntegerValue) {
			$values = $target->values;
			return $values[(int)(string)$parameter->literalValue] ?? $programRegistry->valueRegistry->error(
				$programRegistry->valueRegistry->core->indexOutOfRange(
					$programRegistry->valueRegistry->record(['index' => $parameter])
				)
			);
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
		if ($parameter instanceof StringValue) {
			$values = $target->values;
			return $values[$parameter->literalValue] ?? $programRegistry->valueRegistry->error(
				$programRegistry->valueRegistry->core->mapItemNotFound(
					$programRegistry->valueRegistry->record(['key' => $parameter])
				)
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}