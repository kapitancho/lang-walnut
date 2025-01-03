<?php

namespace Walnut\Lang\Blueprint\Code\Expression;

use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;

interface VariableAssignmentExpression extends Expression {
	public VariableNameIdentifier $variableName { get; }
	public Expression $assignedExpression { get; }
}