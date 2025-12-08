<?php

namespace Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\SetType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\BooleanValue;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\NullValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SetValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

trait Sort {
	use BaseType;

	private function analyseHelper(
		TypeRegistry $typeRegistry,
		ArrayType|MapType|SetType|MutableType $returnType,
		ArrayType|MapType|SetType $targetType,
		Type $parameterType
	): Type {
		$itemType = $targetType->itemType;
		if ($itemType->isSubtypeOf($typeRegistry->string()) || $itemType->isSubtypeOf(
			$typeRegistry->union([
				$typeRegistry->integer(),
				$typeRegistry->real()
			])
		)) {
			$pType = $typeRegistry->union([
				$typeRegistry->null,
				$typeRegistry->record([
					'reverse' => $typeRegistry->boolean
				])
			]);
			if ($parameterType->isSubtypeOf($pType)) {
				return $returnType;
			}
			throw new AnalyserException(sprintf(
				"The parameter type %s is not a subtype of %s",
				$parameterType,
				$pType
			));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	private function executeHelper(
		ProgramRegistry $programRegistry,
		TupleValue|RecordValue|SetValue $target,
		Value $parameter,
		callable $valueBuilder
	): Value {
		if ($parameter instanceof NullValue || (
			$parameter instanceof RecordValue &&
			($rev = $parameter->values['reverse'] ?? null) instanceof BooleanValue
		)) {
			$reverse = isset($rev) ? $rev->literalValue : false;
			$sort = $reverse ? arsort(...) : asort(...);

			$values = $target->values;

			$rawValues = [];
			$hasStrings = false;
			$hasNumbers = false;
			foreach($values as $key => $value) {
				if ($value instanceof StringValue) {
					$hasStrings = true;
				} elseif ($value instanceof IntegerValue || $value instanceof RealValue) {
					$hasNumbers = true;
				} else {
					// @codeCoverageIgnoreStart
					throw new ExecutionException("Invalid target value");
					// @codeCoverageIgnoreEnd
				}
				$rawValues[$key] = (string)$value->literalValue;
			}
			if ($hasStrings) {
				if ($hasNumbers) {
					// @codeCoverageIgnoreStart
					throw new ExecutionException("Invalid target value");
					// @codeCoverageIgnoreEnd
				}
				$sort($rawValues, SORT_STRING);
				return $valueBuilder(array_map(
					fn(string $value) => $programRegistry->valueRegistry->string($value),
					$rawValues
				));
			}
			$sort($rawValues, SORT_NUMERIC);
			return $valueBuilder(array_map(
				fn(string $value) => str_contains((string)$value, '.') ?
					$programRegistry->valueRegistry->real((float)$value) :
					$programRegistry->valueRegistry->integer((int)$value),
				$rawValues
			));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}