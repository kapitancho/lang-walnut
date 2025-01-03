<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

interface NoErrorExpressionNode extends ExpressionNode {
	public ExpressionNode $targetExpression { get; }
}