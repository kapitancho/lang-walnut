<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableValueScope;

interface FunctionValue extends Value {
	public function withSelfReferenceAs(VariableName $variableName): self;
	public function withVariableValueScope(VariableValueScope $variableValueScope): self;

	public function execute(Value $value): Value;
}