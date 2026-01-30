<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface GroupExpressionNode extends ExpressionNode {
	public ExpressionNode $innerExpression { get; }
}