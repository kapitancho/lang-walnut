<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Execution;


use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableValueScope;

interface ExecutionContextFactory {
	public ExecutionContext $emptyExecutionContext { get; }
	public function fromVariableValueScope(VariableValueScope $variableValueScope): ExecutionContext;
}