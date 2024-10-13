<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Type\IntersectionType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\MetaTypeValue;
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

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntersectionType) {
			$types = array_map(
				fn(Type $type) => $this->analyse($type, $parameterType),
				$targetType->types()
			);
			return $this->context->typeRegistry()->intersection($types);
		}
		$type = $targetType instanceof RecordType ? $targetType->asMapType() : $targetType;
		if ($targetType instanceof MetaType && $targetType->value() === MetaTypeValue::Record) {
			$type = $this->context->typeRegistry()->map(
				$this->context->typeRegistry()->any()
			);
		}
		$mapItemNotFound = $this->context->typeRegistry()->sealed(new TypeNameIdentifier("MapItemNotFound"));
		if ($type instanceof MapType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof StringType || $parameterType instanceof StringSubsetType) {
				$returnType = $type->itemType();
				if ($targetType instanceof RecordType && $parameterType instanceof StringSubsetType) {
					$tConv = fn(Type $type): Type => $type instanceof OptionalKeyType ?
						$this->context->typeRegistry()->result($type->valueType(), $mapItemNotFound) :
						$type;
					$returnType = $this->context->typeRegistry()->union(
						array_map(
							static fn(StringValue $value) => $tConv(
								$targetType->types()[$value->literalValue()] ??
								$targetType->restType()
							),
							$parameterType->subsetValues()
						)
					);
					$allKeys = array_filter($parameterType->subsetValues(),
						static fn(StringValue $value) => array_key_exists($value->literalValue(), $targetType->types())
					);
					if (count($allKeys) === count($parameterType->subsetValues())) {
						return $returnType;
					}
				}
				return $this->context->typeRegistry()->result($returnType, $mapItemNotFound);
			}
			// @codeCoverageIgnoreStart
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType    ));
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		$parameterValue = $parameter->value;
		
		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof RecordValue && $parameterValue instanceof StringValue) {
			$values = $targetValue->values();
			$result = $values[$parameterValue->literalValue()] ?? null;
			if ($result) {
				$targetType = $this->toBaseType($target->type);
				$type = $targetType instanceof RecordType ?
					($targetType->types()[$parameterValue->literalValue()] ?? null) :
					$targetType->itemType();
				return new TypedValue($type, $result);
			}
			return TypedValue::forValue($this->context->valueRegistry()->error(
				$this->context->valueRegistry()->sealedValue(
					new TypeNameIdentifier('MapItemNotFound'),
					$this->context->valueRegistry()->record(['key' => $parameterValue])
				)
			));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}