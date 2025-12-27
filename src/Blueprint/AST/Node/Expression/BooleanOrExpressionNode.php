<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

interface BooleanOrExpressionNode extends ExpressionNode {
	public ExpressionNode $first { get; }
	public ExpressionNode $second { get; }

}