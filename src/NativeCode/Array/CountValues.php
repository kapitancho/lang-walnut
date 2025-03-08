<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class CountValues implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof TupleType) {
			$targetType = $targetType->asArrayType();
		}
		if ($targetType instanceof ArrayType) {
			$itemType = $targetType->itemType;
			if ($itemType->isSubtypeOf($programRegistry->typeRegistry->string()) ||
				$itemType->isSubtypeOf($programRegistry->typeRegistry->integer())
			) {
				return $programRegistry->typeRegistry->map(
					$programRegistry->typeRegistry->integer(
						1, max(1, $targetType->range->maxLength)
					),
					min(1, $targetType->range->minLength),
					$targetType->range->maxLength
				);
			}
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
				$rawValues[] = (string)$value->literalValue;
			}
			if ($hasStrings) {
				if ($hasIntegers) {
					// @codeCoverageIgnoreStart
					throw new ExecutionException("Invalid target value");
					// @codeCoverageIgnoreEnd
				}
				$rawValues = array_count_values($rawValues);
				return TypedValue::forValue($programRegistry->valueRegistry->record(array_map(
					fn($value) => $programRegistry->valueRegistry->integer($value),
					$rawValues
				)));
			}
			$rawValues = array_count_values($rawValues);
			return TypedValue::forValue($programRegistry->valueRegistry->record(array_map(
				fn($value) => $programRegistry->valueRegistry->integer($value),
				$rawValues
			)));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}