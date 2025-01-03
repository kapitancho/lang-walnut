<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

interface MatchExpressionPairNode extends ExpressionNode {
	public ExpressionNode $matchExpression { get; }
	public ExpressionNode $valueExpression { get; }
}