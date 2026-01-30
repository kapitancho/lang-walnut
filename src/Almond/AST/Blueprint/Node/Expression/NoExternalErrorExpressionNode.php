<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface NoExternalErrorExpressionNode extends ExpressionNode {
	public ExpressionNode $targetExpression { get; }
}