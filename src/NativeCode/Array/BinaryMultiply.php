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

final readonly class BinaryMultiply implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof TupleType) {
			if ($targetType->restType instanceof NothingType && $parameterType instanceof IntegerType) {
				$minValue = $parameterType->numberRange->min;
				$maxValue = $parameterType->numberRange->max;
				if (
					$minValue !== MinusInfinity::value && $minValue->value >= 0 &&
					$maxValue !== PlusInfinity::value && (string)$maxValue->value === (string)$minValue->value
				) {
					$result = [];
					for ($i = 0; $i < $minValue->value; $i++) {
						$result = array_merge($result, $targetType->types);
					}
					return $typeRegistry->tuple($result);
				}
			}
			$targetType = $targetType->asArrayType();
		}
		if ($targetType instanceof ArrayType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof IntegerType) {
				$minValue = $parameterType->numberRange->min;
				if ($minValue !== MinusInfinity::value && $minValue->value >= 0) {
					return $typeRegistry->array(
						$targetType->itemType,
						$targetType->range->minLength * $minValue->value,
						$targetType->range->maxLength === PlusInfinity::value ||
							$parameterType->numberRange->max === PlusInfinity::value ?
								PlusInfinity::value :
								$targetType->range->maxLength * $parameterType->numberRange->max->value
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
        if ($target instanceof TupleValue) {
			if ($parameter instanceof IntegerValue && $parameter->literalValue >= 0) {
				$result = [];
				for($i = 0; $i < (int)(string)$parameter->literalValue; $i++) {
					$result = array_merge($result, $target->values);
				}
				return $programRegistry->valueRegistry->tuple($result);
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