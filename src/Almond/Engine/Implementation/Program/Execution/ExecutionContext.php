<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Program\Execution;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext as ExecutionContextInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableValueScope;

final readonly class ExecutionContext implements ExecutionContextInterface {
	public function __construct(
		public VariableValueScope $variableValueScope,
		public Value $value
	) {}
	public function withAddedVariableValues(iterable $values): ExecutionContext {
		return clone($this, ['variableValueScope' =>
			$this->variableValueScope->withAddedVariableValues($values)]);
	}
	public function withAddedVariableValue(VariableName $variableName, Value $value): ExecutionContext {
		return clone($this, ['variableValueScope' =>
			$this->variableValueScope->withAddedVariableValue($variableName, $value)]);
	}
	public function withValue(Value $value): ExecutionContext {
		return clone($this, ['value' => $value]);
	}
}