<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program\Execution;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableValueScope;

interface ExecutionContext {
	public VariableValueScope $variableValueScope { get; }
	public Value $value { get; }

	public function withAddedVariableValue(VariableName $variableName, Value $value): self;
	public function withAddedVariableValues(iterable $values): self;

	public function withValue(Value $value): self;
}