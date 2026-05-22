<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface EarlyReturnExpressionNode extends ExpressionNode {
	public ExpressionNode $targetExpression { get; }
	public EarlyReturnExpressionType $type { get; }
}
