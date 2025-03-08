<?php

namespace Walnut\Lang\Blueprint\Code\Execution;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;

interface ExecutionContext extends AnalyserContext {
	public ProgramRegistry $programRegistry { get; }
	public VariableValueScope $variableValueScope { get; }

	public function withAddedVariableValue(
		VariableNameIdentifier $variableName,
		TypedValue $typedValue
	): self;

	public function asExecutionResult(TypedValue $typedValue): ExecutionResult;
}