<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class BinaryIntegerDivide implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		if ($type instanceof ArrayType) {
			$parameterType = $this->toBaseType($parameterType);
			if (
				$parameterType instanceof IntegerType &&
				$parameterType->numberRange->min !== MinusInfinity::value &&
				$parameterType->numberRange->min->value > 0
			) {
				$minL = $type->range->minLength;
				$maxL = $type->range->maxLength;

				$minS = $parameterType->numberRange->min->value;
				$maxS = $parameterType->numberRange->max === PlusInfinity::value ?
					PlusInfinity::value : $parameterType->numberRange->max->value;

				$minO = $maxS !== PlusInfinity::value ? $minL->div($maxS)->floor() : 0;
				$maxO = match(true) {
					$maxL === PlusInfinity::value => PlusInfinity::value,
					$minS > 0 => $maxL->div($minS)->floor(),
					// Should not happen due to earlier checks
					// @codeCoverageIgnoreStart
					default => PlusInfinity::value,
					// @codeCoverageIgnoreEnd
				};

				return $typeRegistry->array(
					$typeRegistry->array(
						$type->itemType,
						$minS,
						$maxS
					),
					$minO,
					$maxO
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
		if ($target instanceof TupleValue && $parameter instanceof IntegerValue) {
			$chunkSize = (int)(string)$parameter->literalValue;
			if ($chunkSize > 0) {
				$chunks = array_chunk($target->values, $chunkSize);
				if (count($chunks) > 0 && count($chunks[array_key_last($chunks)]) < $chunkSize) {
					array_pop($chunks);
				}
				return $programRegistry->valueRegistry->tuple(
					array_map(
						fn(array $chunk): TupleValue =>
						$programRegistry->valueRegistry->tuple($chunk),
						$chunks
					)
				);
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Chunk size must be positive");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target or parameter value");
		// @codeCoverageIgnoreEnd
	}
}
