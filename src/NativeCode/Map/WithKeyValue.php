<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithKeyValue implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof RecordType || $targetType instanceof MapType) {
			if ($parameterType->isSubtypeOf(
				$this->context->typeRegistry->record([
					'key' => $this->context->typeRegistry->string(),
					'value' => $this->context->typeRegistry->any
				])
			)) {
				$keyType = $parameterType->types['key'] ?? null;
				if ($targetType instanceof RecordType) {
					if ($keyType instanceof StringSubsetType && count($keyType->subsetValues) === 1) {
						$keyValue = $keyType->subsetValues[0];
						return $this->context->typeRegistry->record(
							$targetType->types + [
								$keyValue->literalValue => $parameterType->types['value']
							]
						);
					}
					$targetType = $targetType->asMapType();
				}
				$valueType = $parameterType->types['value'] ?? null;
				return $this->context->typeRegistry->map(
					$this->context->typeRegistry->union([
						$targetType->itemType,
						$valueType
					]),
					$targetType->range->minLength,
					$targetType->range->maxLength === PlusInfinity::value ?
						PlusInfinity::value : $targetType->range->maxLength + 1
				);
			}
			// @codeCoverageIgnoreStart
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
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
		if ($targetValue instanceof RecordValue) {
			if ($parameterValue instanceof RecordValue) {
				$p = $parameterValue->values;
				$pKey = $p['key'] ?? null;
				$pValue = $p['value'] ?? null;
				if ($pValue && $pKey instanceof StringValue) {
					$values = $targetValue->values;
					$values[$pKey->literalValue] = $pValue;
					$resultValue = $this->context->valueRegistry->record($values);
					$resultType = $target->type instanceof MapType ?
						$this->context->typeRegistry->map(
							$this->context->typeRegistry->union([
								$target->type->itemType,
								$pValue->type
							]),
							$target->type->range->minLength,
							$target->type->range->maxLength === PlusInfinity::value ?
								PlusInfinity::value : $target->type->range->maxLength + 1
						) : $resultValue->type;
					return new TypedValue($resultType, $resultValue);
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