<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program\Execution;


use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableValueScope;

interface ExecutionContextFactory {
	public ExecutionContext $emptyExecutionContext { get; }
	public function fromVariableValueScope(VariableValueScope $variableValueScope): ExecutionContext;
}