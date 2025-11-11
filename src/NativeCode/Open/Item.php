<?php

namespace Walnut\Lang\NativeCode\Open;

use BcMath\Number;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\OptionalKeyType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\OpenValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Item implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType
	): Type {
		$type = $this->toBaseType($targetType);
		if ($type instanceof OpenType) {
			$valueType = $this->toBaseType($type->valueType);
			$vType = $valueType instanceof TupleType ? $valueType->asArrayType() : $valueType;
			if ($vType instanceof ArrayType) {
				if ($parameterType instanceof IntegerType) {
					$returnType = $vType->itemType;
					if ($valueType instanceof TupleType) {
						$min = $parameterType->numberRange->min;
						$max = $parameterType->numberRange->max;
						if ($min !== MinusInfinity::value && $min->value >= 0) {
							if ($parameterType instanceof IntegerSubsetType) {
								$returnType = $typeRegistry->union(
									array_map(
										static fn(Number $value) => $valueType->types[(string)$value] ?? $valueType->restType,
										$parameterType->subsetValues
									)
								);
							} else {
								$isWithinLimit = $max !== PlusInfinity::value && $max->value < count($valueType->types);
								$returnType = $typeRegistry->union(
									$isWithinLimit ?
										array_slice($valueType->types, (int)(string)$min->value, (int)(string)$max->value - (int)(string)$min->value + 1) :
										[... array_slice($valueType->types, (int)(string)$min->value), $valueType->restType]
								);
							}
						}
					}

					return $parameterType->numberRange->max !== PlusInfinity::value &&
						$vType->range->minLength > $parameterType->numberRange->max->value ?
							$returnType :
							$typeRegistry->result(
								$returnType,
								$typeRegistry->data(
									new TypeNameIdentifier("IndexOutOfRange")
								)
							);
				}
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
			}
			$vType = $valueType instanceof RecordType ? $valueType->asMapType() : $valueType;
			$mapItemNotFound = $typeRegistry->data(new TypeNameIdentifier("MapItemNotFound"));
			if ($vType instanceof MapType) {
				$parameterType = $this->toBaseType($parameterType);
				if ($parameterType instanceof StringType || $parameterType instanceof StringSubsetType) {
					$returnType = $vType->itemType;
					if ($valueType instanceof RecordType && $parameterType instanceof StringSubsetType) {
						$tConv = fn(Type $type): Type => $type instanceof OptionalKeyType ?
							$typeRegistry->result($type->valueType, $mapItemNotFound) :
							$type;
						$returnType = $typeRegistry->union(
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
					return $typeRegistry->result($returnType, $mapItemNotFound);
				}
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
			}
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		if ($target instanceof OpenValue) {
			$baseValue = $target->value;
			if ($baseValue instanceof TupleValue && $parameter instanceof IntegerValue) {
				$values = $baseValue->values;
				$result = $values[(string)$parameter->literalValue] ?? null;
				if ($result !== null) {
					return $result;
				}
				return $programRegistry->valueRegistry->error(
					$programRegistry->valueRegistry->dataValue(
						new TypeNameIdentifier('IndexOutOfRange'),
						$programRegistry->valueRegistry->record(['index' => $parameter])
					)
				);
			}
			if ($baseValue instanceof RecordValue && $parameter instanceof StringValue) {
				$values = $baseValue->values;
				$result = $values[$parameter->literalValue] ?? null;
				if ($result !== null) {
					return $result;
				}
				return $programRegistry->valueRegistry->error(
					$programRegistry->valueRegistry->dataValue(
						new TypeNameIdentifier('MapItemNotFound'),
						$programRegistry->valueRegistry->record(['key' => $parameter])
					)
				);
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}