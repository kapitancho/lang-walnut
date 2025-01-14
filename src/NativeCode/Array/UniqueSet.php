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
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class UniqueSet implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;

		if ($type instanceof ArrayType) {
			$itemType = $type->itemType;
			if ($itemType->isSubtypeOf($programRegistry->typeRegistry->string()) || $itemType->isSubtypeOf(
				$programRegistry->typeRegistry->union([
					$programRegistry->typeRegistry->integer(),
					$programRegistry->typeRegistry->real()
				])
			)) {
				return $programRegistry->typeRegistry->set(
					$type->itemType,
					min(1, $type->range->minLength),
					$type->range->maxLength
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

		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof TupleValue) {
			$values = $targetValue->values;

			$rawValues = [];
			$hasStrings = false;
			$hasNumbers = false;
			foreach($values as $value) {
				if ($value instanceof StringValue) {
					$hasStrings = true;
				} elseif ($value instanceof IntegerValue || $value instanceof RealValue) {
					$hasNumbers = true;
				} else {
					// @codeCoverageIgnoreStart
					throw new ExecutionException("Invalid target value");
					// @codeCoverageIgnoreEnd
				}
				$rawValues[] = (string)$value->literalValue;
			}
			if ($hasStrings) {
				if ($hasNumbers) {
					// @codeCoverageIgnoreStart
					throw new ExecutionException("Invalid target value");
					// @codeCoverageIgnoreEnd
				}
				$rawValues = array_unique($rawValues);
				return TypedValue::forValue($programRegistry->valueRegistry->set(array_map(
					fn($value) => $programRegistry->valueRegistry->string($value),
					$rawValues
				)));
			}
			$rawValues = array_unique($rawValues, SORT_NUMERIC);
			return TypedValue::forValue($programRegistry->valueRegistry->set(array_map(
				fn($value) => str_contains($value, '.') ?
					$programRegistry->valueRegistry->real($value) :
					$programRegistry->valueRegistry->integer($value),
				$rawValues
			)));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}