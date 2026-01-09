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
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithKeyValue implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof RecordType || $targetType instanceof MapType) {
			if ($parameterType->isSubtypeOf(
				$typeRegistry->record([
					'key' => $typeRegistry->string(),
					'value' => $typeRegistry->any
				])
			)) {
				$keyType = $parameterType->types['key'] ?? null;
				if ($targetType instanceof RecordType) {
					if ($keyType instanceof StringSubsetType && count($keyType->subsetValues) === 1) {
						$keyValue = $keyType->subsetValues[0];
						return $typeRegistry->record(
							$targetType->types + [
								$keyValue => $parameterType->types['value']
							]
						);
					}
					$targetType = $targetType->asMapType();
				}
				$keyType = $parameterType->types['key'] ?? null;
				$valueType = $parameterType->types['value'] ?? null;
				return $typeRegistry->map(
					$typeRegistry->union([
						$targetType->itemType,
						$valueType
					]),
					$targetType->range->minLength,
					$targetType->range->maxLength === PlusInfinity::value ?
						PlusInfinity::value : $targetType->range->maxLength + 1,
					$typeRegistry->union([
						$targetType->keyType,
						$keyType
					])
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
			if ($parameter instanceof RecordValue) {
				$p = $parameter->values;
				$pKey = $p['key'] ?? null;
				$pValue = $p['value'] ?? null;
				if ($pValue && $pKey instanceof StringValue) {
					$values = $target->values;
					$values[$pKey->literalValue] = $pValue;
					return $programRegistry->valueRegistry->record($values);
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