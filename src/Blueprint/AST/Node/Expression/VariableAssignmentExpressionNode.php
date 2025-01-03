<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;

interface VariableAssignmentExpressionNode extends ExpressionNode {
	public VariableNameIdentifier $variableName { get; }
	public ExpressionNode $assignedExpression { get; }
}