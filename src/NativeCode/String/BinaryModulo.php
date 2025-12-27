<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class BinaryModulo implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof StringType) {
			$parameterType = $this->toBaseType($parameterType);
			if (
				$parameterType instanceof IntegerType &&
				$parameterType->numberRange->min !== MinusInfinity::value &&
				$parameterType->numberRange->min->value >= 1
			) {
				if (
					$targetType->range->maxLength !== PlusInfinity::value &&
					$parameterType->numberRange->min->value > $targetType->range->maxLength
				) {
					return $targetType;
				}
				if (
					$targetType->range->maxLength !== PlusInfinity::value &&
					(string)$targetType->range->minLength === (string)$targetType->range->maxLength &&
					$parameterType->numberRange->max !== PlusInfinity::value &&
					(string)$parameterType->numberRange->max->value === (string)$parameterType->numberRange->min->value
				) {
					$size = $targetType->range->maxLength->mod($parameterType->numberRange->min->value);
					return $typeRegistry->string($size, $size);
				}

				return $typeRegistry->string(
					0,
					match(true) {
						$parameterType->numberRange->max === PlusInfinity::value => $targetType->range->maxLength,
						$targetType->range->maxLength === PlusInfinity::value => max(
							0,
							$parameterType->numberRange->max->value->sub(1),
						),
						default => max(
							0,
							min(
								$parameterType->numberRange->max->value->sub(1),
								$targetType->range->maxLength
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
		if ($target instanceof StringValue) {
			if ($parameter instanceof IntegerValue) {
				$splitLength = (int)(string)$parameter->literalValue;
				if ($splitLength > 0) {
					$result = mb_str_split($target->literalValue, $splitLength);
					$last = $result[array_key_last($result)] ?? '';
					return $programRegistry->valueRegistry->string(
						mb_strlen($last) < $splitLength ? $last : ''
					);
				}
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