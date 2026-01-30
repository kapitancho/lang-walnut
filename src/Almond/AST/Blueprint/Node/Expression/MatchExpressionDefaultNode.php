<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface MatchExpressionDefaultNode extends ExpressionNode {
	public ExpressionNode $valueExpression { get; }
}