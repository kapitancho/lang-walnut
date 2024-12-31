<?php

namespace Walnut\Lang\Blueprint\Code\Execution;

use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope;
use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;

interface ExecutionContext {
	public VariableValueScope $variableValueScope { get; }

	public function withAddedVariableValue(
		VariableNameIdentifier $variableName,
		TypedValue $typedValue
	): self;

	public function asExecutionResult(TypedValue $typedValue): ExecutionResult;
}