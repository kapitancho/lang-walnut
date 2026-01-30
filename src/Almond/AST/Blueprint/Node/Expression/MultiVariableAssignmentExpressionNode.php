<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\VariableNameNode;

interface MultiVariableAssignmentExpressionNode extends ExpressionNode {
	/** @var array<VariableNameNode> $variableNames */
	public array $variableNames { get; }
	public ExpressionNode $assignedExpression { get; }
}