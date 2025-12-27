<?php

namespace Walnut\Lang\Implementation\Code\NativeCode\Analyser\Function;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\FunctionCompositionMode;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

trait Compose {
	use BaseType;

	private function analyseHelper(
		Type $targetType,
		Type $parameterType,
		FunctionCompositionMode $compositionMode
	): Type {
		$t = $this->toBaseType($targetType);
		if ($t instanceof FunctionType) {
			$p = $this->toBaseType($parameterType);
			if ($p instanceof FunctionType) {
				return $t->composeWith($p, $compositionMode);
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	private function executeHelper(
		Value $target,
		Value $parameter,
		FunctionCompositionMode $compositionMode
	): Value {
		if ($target instanceof FunctionValue) {
			if ($parameter instanceof FunctionValue) {
				return $target->composeWith($parameter, $compositionMode);
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