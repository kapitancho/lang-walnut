<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\IntersectionType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Type\OptionalKeyType;

final readonly class Item implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntersectionType) {
			$types = array_map(
				fn(Type $type) => $this->analyse($typeRegistry, $methodFinder, $type, $parameterType),
				$targetType->types
			);
			return $typeRegistry->intersection($types);
		}
		$type = $targetType instanceof RecordType ? $targetType->asMapType() : $targetType;
		if ($targetType instanceof MetaType && $targetType->value === MetaTypeValue::Record) {
			$type = $typeRegistry->map(
				$typeRegistry->any
			);
		}
		$mapItemNotFound = $typeRegistry->data(new TypeNameIdentifier("MapItemNotFound"));
		if ($type instanceof MapType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof StringType || $parameterType instanceof StringSubsetType) {
				$returnType = $type->itemType;
				if ($targetType instanceof RecordType && $parameterType instanceof StringSubsetType) {
					$tConv = fn(Type $fType): Type => $fType instanceof OptionalKeyType ?
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
				if ($returnType instanceof NothingType) {
					throw new AnalyserException(sprintf("[%s] No property exists that matches the type: %s", __CLASS__, $parameterType));
				}
				return $typeRegistry->result($returnType, $mapItemNotFound);
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
		if ($target instanceof RecordValue && $parameter instanceof StringValue) {
			$values = $target->values;
			$result = $values[$parameter->literalValue] ?? null;
			if ($result !== null) {
				return $result;
			}
			return ($programRegistry->valueRegistry->error(
				$programRegistry->valueRegistry->dataValue(
					new TypeNameIdentifier('MapItemNotFound'),
					$programRegistry->valueRegistry->record(['key' => $parameter])
				)
			));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}