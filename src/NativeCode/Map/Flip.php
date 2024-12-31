<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Flip implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof RecordType) {
			$targetType = $targetType->asMapType();
		}
		if ($targetType instanceof MapType) {
			$itemType = $targetType->itemType;
			if ($itemType->isSubtypeOf($this->context->typeRegistry->string())) {
				return $this->context->typeRegistry->map(
					$this->context->typeRegistry->string(),
					min(1, $targetType->range->minLength),
					$targetType->range->maxLength,
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

		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof RecordValue) {
			$values = $targetValue->values;

			$rawValues = [];
			foreach($values as $key => $value) {
				if ($value instanceof StringValue) {
					$rawValues[$key] = $value->literalValue;
				} else {
					// @codeCoverageIgnoreStart
					throw new ExecutionException("Invalid target value");
					// @codeCoverageIgnoreEnd
				}
			}
			$rawValues = array_flip($rawValues);
			return TypedValue::forValue($this->context->valueRegistry->record(array_map(
				fn($value) => $this->context->valueRegistry->string($value),
				$rawValues
			)));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}