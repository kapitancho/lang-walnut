<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Type\TupleType;

final readonly class CombineAsString implements NativeMethod {
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
			if ($itemType->isSubtypeOf($programRegistry->typeRegistry->string())) {
				if ($parameterType instanceof StringType || $parameterType instanceof StringSubsetType) {
					return $programRegistry->typeRegistry->string(
						$parameterType->range->minLength * max(0, $targetType->range->minLength - 1), /* +
						$itemType->range->minLength * $targetType->range->minLength,
						$parameterType->range->maxLength === PlusInfinity::value ||
						$itemType->range->maxLength === PlusInfinity::value ||
						$targetType->range->maxLength === PlusInfinity::value ? PlusInfinity::value :
							$parameterType->range->maxLength * max(0, $targetType->range->maxLength - 1) +
								$itemType->range->maxLength * $targetType->range->maxLength*/
					);
				}
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
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
		$targetValue = $target;
		$parameterValue = $parameter;
		
				if ($targetValue instanceof TupleValue) {
			if ($parameterValue instanceof StringValue) {
				$result = [];
				foreach($targetValue->values as $value) {
					$value = $value;
					if ($value instanceof StringValue) {
						$result[] = $value->literalValue;
					} else {
						// @codeCoverageIgnoreStart
						throw new ExecutionException("Invalid parameter value");
						// @codeCoverageIgnoreEnd
					}
				}
				$result = implode($parameterValue->literalValue, $result);
				return ($programRegistry->valueRegistry->string($result));
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