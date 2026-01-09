<?php

namespace Walnut\Lang\Implementation\Code\NativeCode\Analyser\String;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

trait StringTrimTrimLeftTrimRight {
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
			if ($parameterType instanceof NullType || $parameterType instanceof StringType) {
				return $typeRegistry->string(0, $targetType->range->maxLength);
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	/**
	 * @param callable(string): string|callable(string, string): string $trimFn
	 */
	private function executeHelper(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter,
		callable $trimFn
	): Value {
		if ($target instanceof StringValue) {
			return $parameter instanceof StringValue ?
				$programRegistry->valueRegistry->string($trimFn($target->literalValue, $parameter->literalValue)) :
				$programRegistry->valueRegistry->string($trimFn($target->literalValue));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}