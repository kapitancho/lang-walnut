<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface MatchExpressionPairNode extends ExpressionNode {
	public ExpressionNode $matchExpression { get; }
	public ExpressionNode $valueExpression { get; }
}