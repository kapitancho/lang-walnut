<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\IntersectionType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Type\OptionalKeyType;

final readonly class Item implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntersectionType) {
			$types = array_map(
				fn(Type $type) => $this->analyse($programRegistry, $type, $parameterType),
				$targetType->types
			);
			return $programRegistry->typeRegistry->intersection($types);
		}
		$type = $targetType instanceof RecordType ? $targetType->asMapType() : $targetType;
		if ($targetType instanceof MetaType && $targetType->value === MetaTypeValue::Record) {
			$type = $programRegistry->typeRegistry->map(
				$programRegistry->typeRegistry->any
			);
		}
		$mapItemNotFound = $programRegistry->typeRegistry->sealed(new TypeNameIdentifier("MapItemNotFound"));
		if ($type instanceof MapType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof StringType || $parameterType instanceof StringSubsetType) {
				$returnType = $type->itemType;
				if ($targetType instanceof RecordType && $parameterType instanceof StringSubsetType) {
					$tConv = fn(Type $type): Type => $type instanceof OptionalKeyType ?
						$programRegistry->typeRegistry->result($type->valueType, $mapItemNotFound) :
						$type;
					$returnType = $programRegistry->typeRegistry->union(
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
				return $programRegistry->typeRegistry->result($returnType, $mapItemNotFound);
			}
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry $programRegistry,
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		$parameterValue = $parameter->value;
		
		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof RecordValue && $parameterValue instanceof StringValue) {
			$values = $targetValue->values;
			$result = $values[$parameterValue->literalValue] ?? null;
			if ($result !== null) {
				$targetType = $this->toBaseType($target->type);
				$type = match(true) {
					$targetType instanceof RecordType => ($targetType->types[$parameterValue->literalValue] ?? $targetType->restType),
					$targetType instanceof MapType => $targetType->itemType,
					default => $result->type
				};
				return new TypedValue($type, $result);
			}
			return TypedValue::forValue($programRegistry->valueRegistry->error(
				$programRegistry->valueRegistry->sealedValue(
					new TypeNameIdentifier('MapItemNotFound'),
					$programRegistry->valueRegistry->record(['key' => $parameterValue])
				)
			));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}