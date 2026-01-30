<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Execution;

use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionContext as ExecutionContextInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionContextFactory as ExecutionContextFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableValueScope as VariableValueScopeInterface;
use Walnut\Lang\Almond\Engine\Implementation\VariableScope\VariableValueScope;

final readonly class ExecutionContextFactory implements ExecutionContextFactoryInterface {

	public ExecutionContextInterface $emptyExecutionContext;

	public function __construct(private Value $initialValue) {
		$this->emptyExecutionContext = new ExecutionContext(
			new VariableValueScope([]), $this->initialValue);
	}

	public function fromVariableValueScope(VariableValueScopeInterface $variableValueScope): ExecutionContextInterface {
		return new ExecutionContext($variableValueScope, $this->initialValue);
	}
}