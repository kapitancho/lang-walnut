<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

interface DirectExpressionNode extends ExpressionNode {
	public ExpressionNode $targetExpression { get; }
}