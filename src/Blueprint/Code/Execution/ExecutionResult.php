<?php

namespace Walnut\Lang\Blueprint\Code\Execution;

use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Value\Value;

interface ExecutionResult extends ExecutionContext {
	public Value $value { get; }

	public function withAddedVariableValue(
		VariableNameIdentifier $variableName,
		Value $value
	): self;

	public function withValue(Value $typedValue): self;
}