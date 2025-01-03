<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

interface NoExternalErrorExpressionNode extends ExpressionNode {
	public ExpressionNode $targetExpression { get; }
}