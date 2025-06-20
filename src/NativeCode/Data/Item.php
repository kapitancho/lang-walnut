<?php

namespace Walnut\Lang\NativeCode\Data;

use BcMath\Number;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\DataType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\OptionalKeyType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\DataValue;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Item implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType
	): Type {
		$type = $this->toBaseType($targetType);
		if ($type instanceof DataType) {
			$valueType = $this->toBaseType($type->valueType);
			$valueType = $valueType instanceof TupleType ? $valueType->asArrayType() : $valueType;
			if ($valueType instanceof ArrayType) {
				if ($parameterType instanceof IntegerType) {
					$returnType = $valueType->itemType;
					if ($targetType instanceof TupleType) {
						$min = $parameterType->numberRange->min;
						$max = $parameterType->numberRange->max;
						if ($min !== MinusInfinity::value && $min->value >= 0) {
							if ($parameterType instanceof IntegerSubsetType) {
								$returnType = $programRegistry->typeRegistry->union(
									array_map(
										static fn(Number $value) => $targetType->types[(string)$value] ?? $targetType->restType,
										$parameterType->subsetValues
									)
								);
							} elseif ($parameterType instanceof IntegerType) {
								$isWithinLimit = $max !== PlusInfinity::value && $max->value < count($targetType->types);
								$returnType = $programRegistry->typeRegistry->union(
									$isWithinLimit ?
										array_slice($targetType->types, (int)(string)$min->value, (int)(string)$max->value - (int)(string)$min->value + 1) :
										[... array_slice($targetType->types, (int)(string)$min->value), $targetType->restType]
								);
							}
						}
					}

					return $parameterType->numberRange->max !== PlusInfinity::value &&
						$valueType->range->minLength > $parameterType->numberRange->max->value ?
							$returnType :
							$programRegistry->typeRegistry->result(
								$returnType,
								$programRegistry->typeRegistry->data(
									new TypeNameIdentifier("IndexOutOfRange")
								)
							);
				}
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
			}
			$vType = $valueType instanceof RecordType ? $valueType->asMapType() : $valueType;
			$mapItemNotFound = $programRegistry->typeRegistry->data(new TypeNameIdentifier("MapItemNotFound"));
			if ($vType instanceof MapType) {
				$parameterType = $this->toBaseType($parameterType);
				if ($parameterType instanceof StringType || $parameterType instanceof StringSubsetType) {
					$returnType = $vType->itemType;
					if ($valueType instanceof RecordType && $parameterType instanceof StringSubsetType) {
						$tConv = fn(Type $type): Type => $type instanceof OptionalKeyType ?
							$programRegistry->typeRegistry->result($type->valueType, $mapItemNotFound) :
							$type;
						$returnType = $programRegistry->typeRegistry->union(
							array_map(
								static fn(string $value) => $tConv(
									$valueType->types[$value] ??
									$valueType->restType
								),
								$parameterType->subsetValues
							)
						);
						$allKeys = array_filter($parameterType->subsetValues,
							static fn(string $value) => array_key_exists($value, $valueType->types)
						);
						if (count($allKeys) === count($parameterType->subsetValues)) {
							return $returnType;
						}
					}
					return $programRegistry->typeRegistry->result($returnType, $mapItemNotFound);
				}
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
			}
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry        $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$targetValue = $target;
		$parameterValue = $parameter;
		
		if ($targetValue instanceof DataValue) {
			$baseValue = $targetValue->value;
			if ($baseValue instanceof TupleValue && $parameterValue instanceof IntegerValue) {
				$values = $baseValue->values;
				$result = $values[(string)$parameterValue->literalValue] ?? null;
				if ($result !== null) {
					return $result;
				}
				return ($programRegistry->valueRegistry->error(
					$programRegistry->valueRegistry->dataValue(
						new TypeNameIdentifier('IndexOutOfRange'),
						$programRegistry->valueRegistry->record(['index' => $parameterValue])
					)
				));
			}
			if ($baseValue instanceof RecordValue && $parameterValue instanceof StringValue) {
				$values = $baseValue->values;
				$result = $values[$parameterValue->literalValue] ?? null;
				if ($result !== null) {
					return $result;
				}
				return ($programRegistry->valueRegistry->error(
					$programRegistry->valueRegistry->dataValue(
						new TypeNameIdentifier('MapItemNotFound'),
						$programRegistry->valueRegistry->record(['key' => $parameterValue])
					)
				));
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}