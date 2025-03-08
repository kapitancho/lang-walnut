<?php

namespace Walnut\Lang\Blueprint\Function;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;

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
		TypedValue|null $targetValue,
		TypedValue|null $parameterValue,
		VariableNameIdentifier|null $parameterName,
		TypedValue|null $dependencyValue
	): ExecutionContext;

}