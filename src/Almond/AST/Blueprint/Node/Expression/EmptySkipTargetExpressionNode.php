<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface EmptySkipTargetExpressionNode extends ExpressionNode {
	public string $skipTargetId { get; }
	public ExpressionNode $targetExpression { get; }
}
