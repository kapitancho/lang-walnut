<?php

namespace Walnut\Lang\Implementation\Code\Execution;

use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext as ExecutionContextInterface;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;

final readonly class ExecutionContext implements ExecutionContextInterface {

	public function __construct(
		public ProgramRegistry $programRegistry,
		public VariableValueScope $variableValueScope
	) {}

	public function withAddedVariableValue(VariableNameIdentifier $variableName, TypedValue $typedValue): self {
		return new self(
			$this->programRegistry,
			$this->variableValueScope->withAddedVariableValue($variableName, $typedValue)
		);
	}

	public function asExecutionResult(TypedValue $typedValue): ExecutionResult {
		return new ExecutionResult(
			$this->programRegistry,
			$this->variableValueScope,
			$typedValue
		);
	}
}