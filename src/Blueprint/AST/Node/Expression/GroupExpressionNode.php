<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

interface GroupExpressionNode extends ExpressionNode {
	public ExpressionNode $innerExpression { get; }
}