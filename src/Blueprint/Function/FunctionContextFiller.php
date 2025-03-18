<?php

namespace Walnut\Lang\Blueprint\Function;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

interface FunctionContextFiller {

	public function fillAnalyserContext(
		AnalyserContext $analyserContext,
		Type $targetType,
		Type $parameterType,
		VariableNameIdentifier|null $parameterName,
		Type $dependencyType
	): AnalyserContext;

	public function fillExecutionContext(
		ExecutionContext $executionContext,
		Type $targetType,
		Value|null $targetValue,
		Type $parameterType,
		Value|null $parameterValue,
		VariableNameIdentifier|null $parameterName,
		Type $dependencyType,
		Value|null $dependencyValue
	): ExecutionContext;

}