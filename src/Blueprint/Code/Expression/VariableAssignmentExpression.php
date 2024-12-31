<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;

interface VariableAssignmentExpression extends Expression {
	public VariableNameIdentifier $variableName { get; }
	public Expression $assignedExpression { get; }
}