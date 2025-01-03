<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

interface MatchExpressionDefaultNode extends ExpressionNode {
	public ExpressionNode $valueExpression { get; }
}