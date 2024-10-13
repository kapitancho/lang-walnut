<?php

namespace Walnut\Lang\NativeCode\Sealed;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\SealedValue;
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
		if ($targetType instanceof SealedType) {
			$mapItemNotFound = $this->context->typeRegistry()->sealed(new TypeNameIdentifier("MapItemNotFound"));
			$recordType = $targetType->valueType();
			$type = $recordType->asMapType();
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof StringType || $parameterType instanceof StringSubsetType) {
				$returnType = $type->itemType();
				if ($parameterType instanceof StringSubsetType) {
					$tConv = fn(Type $type): Type => $type instanceof OptionalKeyType ?
						$this->context->typeRegistry()->result($type->valueType(), $mapItemNotFound) :
						$type;
					$returnType = $this->context->typeRegistry()->union(
						array_map(
							static fn(StringValue $value) => $tConv(
								$recordType->types()[$value->literalValue()] ??
								$recordType->restType()
							),
							$parameterType->subsetValues()
						)
					);
					$allKeys = array_filter($parameterType->subsetValues(),
						static fn(StringValue $value) => array_key_exists($value->literalValue(), $recordType->types())
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
		
		if ($targetValue instanceof SealedValue) {
			$values = $targetValue->value()->values();
			$result = $values[$parameterValue->literalValue()] ?? null;
			if ($result) {
				$targetType = $this->toBaseType($target->type)->valueType();
				$type = $targetType->types()[$parameterValue->literalValue()] ?? null;
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