<?php

namespace Walnut\Lang\Implementation\Code\NativeCode\Analyser\String;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

trait StringChunk {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
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
				return $typeRegistry->array(
					$typeRegistry->string(
						min(1, $targetType->range->minLength),
						$parameterType->numberRange->max === PlusInfinity::value ?
							PlusInfinity::value : $parameterType->numberRange->max->value
					),
					match(true) {
						$parameterType->numberRange->max === PlusInfinity::value =>
							$targetType->range->minLength > 0 ? 1 : 0,
						default => $targetType->range->minLength->div($parameterType->numberRange->max->value)->ceil()
					},
					$targetType->range->maxLength === PlusInfinity::value ?
						PlusInfinity::value :
						$targetType->range->maxLength->div($parameterType->numberRange->min->value)->ceil()
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
					return $programRegistry->valueRegistry->tuple(
						array_map(fn(string $piece): StringValue =>
						$programRegistry->valueRegistry->string($piece), $result)
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