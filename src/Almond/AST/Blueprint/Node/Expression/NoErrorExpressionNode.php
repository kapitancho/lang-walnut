<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface NoErrorExpressionNode extends ExpressionNode {
	public ExpressionNode $targetExpression { get; }
}