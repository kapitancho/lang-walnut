<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;

interface MultiVariableAssignmentExpressionNode extends ExpressionNode {
	/** @var array<VariableNameIdentifier> $variableNames */
	public array $variableNames { get; }
	public ExpressionNode $assignedExpression { get; }
}