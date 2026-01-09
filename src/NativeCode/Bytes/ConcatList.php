<?php

namespace Walnut\Lang\NativeCode\Bytes;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\BytesType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\BytesValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Type\TupleType;

final readonly class ConcatList implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof BytesType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof TupleType) {
				$parameterType = $parameterType->asArrayType();
			}
			if ($parameterType instanceof ArrayType) {
				$itemType = $this->toBaseType($parameterType->itemType);
				if ($itemType instanceof BytesType) {
					return $typeRegistry->bytes(
						$targetType->range->minLength + $parameterType->range->minLength * $itemType->range->minLength,
						$targetType->range->maxLength === PlusInfinity::value ||
						$parameterType->range->maxLength === PlusInfinity::value ||
						$itemType->range->maxLength === PlusInfinity::value ? PlusInfinity::value :
						$targetType->range->maxLength + $parameterType->range->maxLength * $itemType->range->maxLength,
					);
				}
				if ($itemType->isSubtypeOf($typeRegistry->bytes())) {
					return $typeRegistry->bytes();
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
		if ($target instanceof BytesValue) {
			if ($parameter instanceof TupleValue) {
				$result = $target->literalValue;
				foreach($parameter->values as $value) {
					if ($value instanceof BytesValue) {
						$result .= $value->literalValue;
					} else {
						// @codeCoverageIgnoreStart
						throw new ExecutionException("Invalid parameter value");
						// @codeCoverageIgnoreEnd
					}
				}
				return $programRegistry->valueRegistry->bytes($result);
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
