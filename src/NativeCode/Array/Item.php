<?php

namespace Walnut\Lang\NativeCode\Array;

use BcMath\Number;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;


final readonly class Item implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType
	): Type {
		$targetType = $this->toBaseType($targetType);
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		if ($type instanceof ArrayType) {
			if ($parameterType instanceof IntegerType) {
				$returnType = $type->itemType;
				if ($targetType instanceof TupleType) {
					$min = $parameterType->numberRange->min;
					$max = $parameterType->numberRange->max;
					if ($min !== MinusInfinity::value && $min->value >= 0) {
						if ($parameterType instanceof IntegerSubsetType) {
							$returnType = $programRegistry->typeRegistry->union(
								array_map(
									static fn(Number $value) =>
										$targetType->types[(string)$value] ?? $targetType->restType,
									$parameterType->subsetValues
								)
							);
						} elseif ($parameterType instanceof IntegerType) {
							$isWithinLimit = $max !== PlusInfinity::value && $max->value < count($targetType->types);
							$returnType = $programRegistry->typeRegistry->union(
								$isWithinLimit ?
								array_slice($targetType->types, (int)(string)$min->value, (int)(string)$max->value - (int)(string)$min->value + 1) :
								[... array_slice($targetType->types, (int)(string)$min->value), $targetType->restType]
							);
						}
					}
				}

				return $parameterType->numberRange->max !== PlusInfinity::value &&
					$type->range->minLength > $parameterType->numberRange->max->value ?
					$returnType :
					$programRegistry->typeRegistry->result(
						$returnType,
						$programRegistry->typeRegistry->data(
							new TypeNameIdentifier("IndexOutOfRange")
						)
					);
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry        $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$targetValue = $target;
		$parameterValue = $parameter;
		
		if ($targetValue instanceof TupleValue && $parameterValue instanceof IntegerValue) {
			$values = $targetValue->values;
			$result = $values[(string)$parameterValue->literalValue] ?? null;
			if ($result !== null) {
				return $result;
			}
			return ($programRegistry->valueRegistry->error(
				$programRegistry->valueRegistry->dataValue(
					new TypeNameIdentifier('IndexOutOfRange'),
					$programRegistry->valueRegistry->record(['index' => $parameterValue])
				)
			));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}