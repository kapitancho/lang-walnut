<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface BooleanOrExpressionNode extends ExpressionNode {
	public ExpressionNode $first { get; }
	public ExpressionNode $second { get; }

}