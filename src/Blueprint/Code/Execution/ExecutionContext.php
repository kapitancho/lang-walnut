<?php

namespace Walnut\Lang\Blueprint\Code\Execution;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Value\Value;

interface ExecutionContext extends AnalyserContext {
	public DependencyContainer $dependencyContainer { get; }
	public ValueRegistry $valueRegistry { get; }
	public VariableValueScope $variableValueScope { get; }

	public function withAddedVariableValue(
		VariableNameIdentifier $variableName,
		Value $value
	): self;

	public function asExecutionResult(Value $typedValue): ExecutionResult;
}