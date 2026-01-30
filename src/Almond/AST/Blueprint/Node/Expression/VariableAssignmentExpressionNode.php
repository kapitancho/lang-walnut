<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\VariableNameNode;

interface VariableAssignmentExpressionNode extends ExpressionNode {
	public VariableNameNode $variableName { get; }
	public ExpressionNode $assignedExpression { get; }
}