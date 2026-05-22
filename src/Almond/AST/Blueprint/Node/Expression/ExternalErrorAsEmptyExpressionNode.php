<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface ExternalErrorAsEmptyExpressionNode extends ExpressionNode {
	public ExpressionNode $targetExpression { get; }
}