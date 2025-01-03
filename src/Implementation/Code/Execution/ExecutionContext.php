<?php

namespace Walnut\Lang\Implementation\Code\Execution;

use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext as ExecutionContextInterface;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;

final readonly class ExecutionContext implements ExecutionContextInterface {

	public function __construct(
		public VariableValueScope $variableValueScope
	) {}

	public function withAddedVariableValue(VariableNameIdentifier $variableName, TypedValue $typedValue): self {
		return new self(
			$this->variableValueScope->withAddedVariableValue($variableName, $typedValue)
		);
	}

	public function asExecutionResult(TypedValue $typedValue): ExecutionResult {
		return new ExecutionResult(
			$this->variableValueScope,
			$typedValue
		);
	}
}