<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface ErrorAsEmptyExpressionNode extends ExpressionNode {
	public ExpressionNode $targetExpression { get; }
}