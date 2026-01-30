<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Value;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableValueScope;

interface FunctionValue extends Value {
	public function withSelfReferenceAs(VariableName $variableName): self;
	public function withVariableValueScope(VariableValueScope $variableValueScope): self;

	public function execute(Value $value): Value;
}