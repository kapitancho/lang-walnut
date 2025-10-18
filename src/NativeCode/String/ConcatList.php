<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
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

final readonly class ConcatList implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof StringType || $targetType instanceof StringSubsetType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof TupleType) {
				$parameterType = $parameterType->asArrayType();
			}
			if ($parameterType instanceof ArrayType) {
				$itemType = $this->toBaseType($parameterType->itemType);
				if ($itemType instanceof StringType || $itemType instanceof StringSubsetType) {
					return $programRegistry->typeRegistry->string(
						$targetType->range->minLength +  $parameterType->range->minLength * $itemType->range->minLength,
						$targetType->range->maxLength === PlusInfinity::value ||
						$parameterType->range->maxLength === PlusInfinity::value ||
						$itemType->range->maxLength === PlusInfinity::value ? PlusInfinity::value :
						$targetType->range->maxLength +  $parameterType->range->maxLength * $itemType->range->maxLength,
					);
				}
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
			if ($parameter instanceof TupleValue) {
				$result = $target->literalValue;
				foreach($parameter->values as $value) {
					if ($value instanceof StringValue) {
						$result .= $value->literalValue;
					} else {
						// @codeCoverageIgnoreStart
						throw new ExecutionException("Invalid parameter value");
						// @codeCoverageIgnoreEnd
					}
				}
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