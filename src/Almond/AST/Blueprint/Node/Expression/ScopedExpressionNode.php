<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface ScopedExpressionNode extends ExpressionNode {
	public ExpressionNode $targetExpression { get; }
}