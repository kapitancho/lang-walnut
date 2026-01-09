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
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class BinaryModulo implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if (
			$targetType instanceof TupleType &&
			$targetType->restType instanceof NothingType &&
			$parameterType instanceof IntegerType &&
			$parameterType->numberRange->min !== MinusInfinity::value &&
			$parameterType->numberRange->min->value > 0 &&
			$parameterType->numberRange->max !== PlusInfinity::value &&
			(string)$parameterType->numberRange->max->value === (string)$parameterType->numberRange->min->value
		) {
			$l = (int)(string)$parameterType->numberRange->max->value;
			$skip = intdiv(count($targetType->types), $l) * $l;
			return $typeRegistry->tuple(
				array_slice(
					$targetType->types,
					$skip
				)
			);
		}
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		if ($type instanceof ArrayType) {
			$parameterType = $this->toBaseType($parameterType);
			if (
				$parameterType instanceof IntegerType &&
				$parameterType->numberRange->min !== MinusInfinity::value &&
				$parameterType->numberRange->min->value > 0
			) {
				if (
					$type->range->maxLength !== PlusInfinity::value &&
					$parameterType->numberRange->min->value > $type->range->maxLength
				) {
					return $targetType;
				}
				if (
					$type->range->maxLength !== PlusInfinity::value &&
					(string)$type->range->minLength === (string)$type->range->maxLength &&
					$parameterType->numberRange->max !== PlusInfinity::value &&
					(string)$parameterType->numberRange->max->value === (string)$parameterType->numberRange->min->value
				) {
					$size = $type->range->maxLength->mod($parameterType->numberRange->min->value);
					return $typeRegistry->array($type->itemType, $size, $size);
				}
				return $typeRegistry->array(
					$type->itemType,
					0,
					match(true) {
						$parameterType->numberRange->max === PlusInfinity::value => $type->range->maxLength,
						$type->range->maxLength === PlusInfinity::value => max(
							0,
							$parameterType->numberRange->max->value->sub(1),
						),
						default => max(
							0,
							min(
								$parameterType->numberRange->max->value->sub(1),
								$type->range->maxLength
							)
						)
					}
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
				$last = $chunks[array_key_last($chunks)];

				return $programRegistry->valueRegistry->tuple(
					count($chunks) > 0 && count($last) < $chunkSize ?
						$last : []
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
