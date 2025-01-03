<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;

interface VariableAssignmentExpressionNode extends ExpressionNode {
	public VariableNameIdentifier $variableName { get; }
	public ExpressionNode $assignedExpression { get; }
}