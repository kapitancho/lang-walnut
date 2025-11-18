<?php

namespace Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite\Array;


use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\NullValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

trait ArrayUniqueUniqueSet {
	use BaseType;

	/**
	 * @param callable(Type, Number, Number|PlusInfinity): Type $typeCreatorFn
	 */
	private function analyseHelper(
		TypeRegistry $typeRegistry,
		Type $targetType,
		Type $parameterType,
		callable $typeCreatorFn
	): Type {
		$targetType = $this->toBaseType($targetType);
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;

		if ($type instanceof ArrayType) {
			$itemType = $type->itemType;
			if ($itemType->isSubtypeOf($typeRegistry->string()) || $itemType->isSubtypeOf(
				$typeRegistry->union([
					$typeRegistry->integer(),
					$typeRegistry->real()
				])
			)) {
				if ($parameterType instanceof NullType) {
					return $typeCreatorFn(
						$type->itemType,
						min(1, $type->range->minLength),
						$type->range->maxLength
					);
				}
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType::class));
			}
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	/** @return array<Value> */
	private function executeHelper(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): array {
		if ($target instanceof TupleValue) {
			if ($parameter instanceof NullValue) {
				$values = $target->values;

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
					return array_map(
						fn(string $value) => $programRegistry->valueRegistry->string($value),
						$rawValues
					);
				}
				$rawValues = array_unique($rawValues, SORT_NUMERIC);
				return array_map(
					fn(string $value) => str_contains($value, '.') ?
						$programRegistry->valueRegistry->real((float)$value) :
						$programRegistry->valueRegistry->integer((int)$value),
					$rawValues
				);
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