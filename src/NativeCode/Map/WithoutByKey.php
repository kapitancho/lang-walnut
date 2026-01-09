<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\OptionalKeyType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Type\Helper\SubsetTypeHelper;

final readonly class WithoutByKey implements NativeMethod {
	use BaseType, SubsetTypeHelper;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		$parameterType = $this->toBaseType($parameterType);
		if ($targetType instanceof RecordType) {
			if ($parameterType instanceof StringSubsetType) {
				$recordTypes = $targetType->types;
				if (
					count($parameterType->subsetValues) === 1 &&
					array_key_exists($parameterType->subsetValues[0], $recordTypes)
				) {
					$elementType = $recordTypes[$parameterType->subsetValues[0]];
					unset($recordTypes[$parameterType->subsetValues[0]]);
					return $typeRegistry->record([
						'element' => $elementType,
						'map' => $typeRegistry->record(
							$recordTypes,
							$targetType->restType
						)
					]);
				} else {
					$canBeMissing = false;
					$elementTypes = [];
					foreach ($parameterType->subsetValues as $subsetValue) {
						if (array_key_exists($subsetValue, $recordTypes)) {
							if ($recordTypes[$subsetValue] instanceof OptionalKeyType) {
								$elementTypes[] = $recordTypes[$subsetValue]->valueType;
								$canBeMissing = true;
							} else {
								$elementTypes[] = $recordTypes[$subsetValue];
								$recordTypes[$subsetValue] = $typeRegistry->optionalKey(
									$recordTypes[$subsetValue]
								);
							}
						} else {
							$canBeMissing = true;
							$elementTypes[] = $targetType->restType;
						}
					}
					$returnType = $typeRegistry->record([
						'element' => $typeRegistry->union($elementTypes),
						'map' => $typeRegistry->record(
							array_map(
								fn(Type $type): OptionalKeyType => $type instanceof OptionalKeyType ?
									$type :
									$typeRegistry->optionalKey($type),
								$targetType->types
							),
							$targetType->restType
						)
					]);
					return $canBeMissing ? $typeRegistry->result(
						$returnType,
						$typeRegistry->core->mapItemNotFound
					) : $returnType;
				}
			}
			$targetType = $targetType->asMapType();
		}
		if ($targetType instanceof MapType) {
			if ($parameterType instanceof StringType) {
				$keyType = $targetType->keyType;
				if ($keyType instanceof StringSubsetType && $parameterType instanceof StringSubsetType) {
					$keyType = $this->stringSubsetDiff($typeRegistry, $keyType, $parameterType);
				}
				$returnType = $typeRegistry->record([
					'element' => $targetType->itemType,
					'map' => $typeRegistry->map(
						$targetType->itemType,
						$targetType->range->maxLength === PlusInfinity::value ?
							$targetType->range->minLength : max(0,
							min(
								$targetType->range->minLength - 1,
								$targetType->range->maxLength - 1
							)),
						$targetType->range->maxLength === PlusInfinity::value ?
							PlusInfinity::value : max($targetType->range->maxLength - 1, 0),
						$keyType
					)
				]);
				return $typeRegistry->result(
					$returnType,
					$typeRegistry->core->mapItemNotFound
				);
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
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
		if ($target instanceof RecordValue) {
			if ($parameter instanceof StringValue) {
				$values = $target->values;
				if (!isset($values[$parameter->literalValue])) {
					return $programRegistry->valueRegistry->error(
						$programRegistry->valueRegistry->core->mapItemNotFound(
							$programRegistry->valueRegistry->record(['key' => $parameter])
						)
					);
				}
				$val = $values[$parameter->literalValue];
				unset($values[$parameter->literalValue]);
				return $programRegistry->valueRegistry->record([
					'element' => $val,
					'map' => $programRegistry->valueRegistry->record($values)
				]);
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}