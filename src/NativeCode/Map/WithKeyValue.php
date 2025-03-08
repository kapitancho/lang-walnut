<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithKeyValue implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof RecordType || $targetType instanceof MapType) {
			if ($parameterType->isSubtypeOf(
				$programRegistry->typeRegistry->record([
					'key' => $programRegistry->typeRegistry->string(),
					'value' => $programRegistry->typeRegistry->any
				])
			)) {
				$keyType = $parameterType->types['key'] ?? null;
				if ($targetType instanceof RecordType) {
					if ($keyType instanceof StringSubsetType && count($keyType->subsetValues) === 1) {
						$keyValue = $keyType->subsetValues[0];
						return $programRegistry->typeRegistry->record(
							$targetType->types + [
								$keyValue => $parameterType->types['value']
							]
						);
					}
					$targetType = $targetType->asMapType();
				}
				$valueType = $parameterType->types['value'] ?? null;
				return $programRegistry->typeRegistry->map(
					$programRegistry->typeRegistry->union([
						$targetType->itemType,
						$valueType
					]),
					$targetType->range->minLength,
					$targetType->range->maxLength === PlusInfinity::value ?
						PlusInfinity::value : $targetType->range->maxLength + 1
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
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		$parameterValue = $parameter->value;
		
		if ($targetValue instanceof RecordValue) {
			if ($parameterValue instanceof RecordValue) {
				$p = $parameterValue->values;
				$pKey = $p['key'] ?? null;
				$pValue = $p['value'] ?? null;
				if ($pValue && $pKey instanceof StringValue) {
					$values = $targetValue->values;
					$values[$pKey->literalValue] = $pValue;
					$resultValue = $programRegistry->valueRegistry->record($values);
					$resultType = $target->type instanceof MapType ?
						$programRegistry->typeRegistry->map(
							$programRegistry->typeRegistry->union([
								$target->type->itemType,
								$pValue->type
							]),
							$target->type->range->minLength,
							$target->type->range->maxLength === PlusInfinity::value ?
								PlusInfinity::value : $target->type->range->maxLength + 1
						) : $resultValue->type;

					return TypedValue::forValue($resultValue)->withType($resultType);
				}
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