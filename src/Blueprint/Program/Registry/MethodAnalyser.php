<?php

namespace Walnut\Lang\Blueprint\Program\Registry;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

interface MethodAnalyser {
	/** @throws AnalyserException */
	public function analyseMethod(
		Type $targetType,
		MethodNameIdentifier $methodName,
		Type $parameterType
	): Type;
}