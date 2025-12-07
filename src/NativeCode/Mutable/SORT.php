<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class SORT implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof MutableType) {
			$valueType = $this->toBaseType($targetType->valueType);
			if ($valueType instanceof ArrayType) {
				$itemType = $valueType->itemType;
				if ($itemType->isSubtypeOf($typeRegistry->string()) || $itemType->isSubtypeOf(
					$typeRegistry->union([
						$typeRegistry->integer(),
						$typeRegistry->real()
					])
				)) {
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
		if ($target instanceof MutableValue) {
			$v = $target->value;
			if ($v instanceof TupleValue) {
				$values = $v->values;

				$rawValues = [];
				$hasStrings = false;
				$hasNumbers = false;
				foreach ($values as $value) {
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
					sort($rawValues, SORT_STRING);
					$target->value = $programRegistry->valueRegistry->tuple(array_map(
						fn(string $value) => $programRegistry->valueRegistry->string($value),
						$rawValues
					));
					return $target;
				}
				sort($rawValues, SORT_NUMERIC);
				$target->value = $programRegistry->valueRegistry->tuple(array_map(
					fn(string $value) => str_contains((string)$value, '.') ?
						$programRegistry->valueRegistry->real((float)$value) :
						$programRegistry->valueRegistry->integer((int)$value),
					$rawValues
				));
				return $target;
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}