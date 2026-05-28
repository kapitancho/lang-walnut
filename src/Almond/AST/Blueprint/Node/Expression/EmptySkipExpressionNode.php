<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface EmptySkipExpressionNode extends ExpressionNode {
	public string $skipTargetId { get; }
	public ExpressionNode $targetExpression { get; }
}
