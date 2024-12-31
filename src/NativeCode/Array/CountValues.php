<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class CountValues implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		if ($targetType instanceof ArrayType) {
			$itemType = $targetType->itemType;
			if ($itemType->isSubtypeOf($this->context->typeRegistry->string()) ||
				$itemType->isSubtypeOf($this->context->typeRegistry->integer())
			) {
				return $this->context->typeRegistry->map(
					$this->context->typeRegistry->integer(
						1, $targetType->range->maxLength
					),
					min(1, $targetType->range->minLength),
					$targetType->range->maxLength
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
		if ($targetValue instanceof TupleValue) {
			$values = $targetValue->values;

			$rawValues = [];
			$hasStrings = false;
			$hasIntegers = false;
			foreach($values as $value) {
				if ($value instanceof StringValue) {
					$hasStrings = true;
				} elseif ($value instanceof IntegerValue) {
					$hasIntegers = true;
				} else {
					// @codeCoverageIgnoreStart
					throw new ExecutionException("Invalid target value");
					// @codeCoverageIgnoreEnd
				}
				$rawValues[] = $value->literalValue;
			}
			if ($hasStrings) {
				if ($hasIntegers) {
					// @codeCoverageIgnoreStart
					throw new ExecutionException("Invalid target value");
					// @codeCoverageIgnoreEnd
				}
				$rawValues = array_count_values($rawValues);
				return TypedValue::forValue($this->context->valueRegistry->record(array_map(
					fn($value) => $this->context->valueRegistry->string($value),
					$rawValues
				)));
			}
			$rawValues = array_count_values($rawValues);
			return TypedValue::forValue($this->context->valueRegistry->record(array_map(
				fn($value) => is_float($value) ?
					$this->context->valueRegistry->real($value) :
					$this->context->valueRegistry->integer($value),
				$rawValues
			)));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}