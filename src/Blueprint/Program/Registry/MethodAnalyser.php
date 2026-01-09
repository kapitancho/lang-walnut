<?php

namespace Walnut\Lang\Blueprint\Program\Registry;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Type\Type;

interface MethodAnalyser extends MethodRegistry {
	/** @throws AnalyserException */
	public function analyseMethod(
		Type $targetType,
		MethodNameIdentifier $methodName,
		Type $parameterType
	): Type;

	public function safeAnalyseMethod(
		Type $targetType,
		MethodNameIdentifier $methodName,
		Type $parameterType
	): Type|UnknownMethod;
}