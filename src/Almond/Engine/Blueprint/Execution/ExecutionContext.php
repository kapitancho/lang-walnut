<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Execution;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableValueScope;

interface ExecutionContext {
	public VariableValueScope $variableValueScope { get; }
	public Value $value { get; }

	public function withAddedVariableValue(VariableName $variableName, Value $value): self;
	public function withAddedVariableValues(iterable $values): self;

	public function withValue(Value $value): self;
}