<?php

namespace Walnut\Lang\NativeCode\Data;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\DataType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\DataValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Code\NativeCode\ValueConstructor;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class With implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType
	): Type {
		$type = $this->toBaseType($targetType);
		if ($type instanceof DataType) {
			$valueType = $this->toBaseType($type->valueType);

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
						return $targetType;
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
					return $targetType;
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
					return $targetType;
				}
			}
			if ($valueType instanceof MapType) {
				$pType = $pType instanceof RecordType ? $pType->asMapType() : $pType;
				if ($pType instanceof MapType) {
					if (!$pType->itemType->isSubtypeOf($valueType->itemType)) {
						throw new AnalyserException(
							sprintf("Cannot call 'with' on Map type %s with a parameter of Map type %s due to incompatible item type",
								$targetType, $parameterType)
						);
					}
					if (
						$valueType->range->maxLength === PlusInfinity::value
					) {
						return $targetType;
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
					return $targetType;
				}
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
		$targetValue = $target;
		$parameterValue = $parameter;
		
		if ($targetValue instanceof DataValue) {
			$baseValue = $targetValue->value;

			$construct = function(Value $parameterValue) use ($programRegistry, $baseValue, $targetValue) {
				return $programRegistry->valueRegistry->dataValue($targetValue->type->name, $parameterValue);
			};

			if ($baseValue instanceof TupleValue && $parameterValue instanceof TupleValue) {
				$values = $baseValue->values;
				foreach ($parameter->values as $index => $value) {
					$values[$index] = $value;
				}
				return $construct($programRegistry->valueRegistry->tuple($values));
			}
			if ($baseValue instanceof RecordValue && $parameterValue instanceof RecordValue) {
				$values = $baseValue->values;
				foreach ($parameter->values as $key => $value) {
					$values[$key] = $value;
				}
				return $construct($programRegistry->valueRegistry->record($values));
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}