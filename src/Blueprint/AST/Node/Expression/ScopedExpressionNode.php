<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

interface ScopedExpressionNode extends ExpressionNode {
	public ExpressionNode $targetExpression { get; }
}