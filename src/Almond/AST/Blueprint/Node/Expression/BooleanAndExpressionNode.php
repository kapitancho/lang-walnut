<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface BooleanAndExpressionNode extends ExpressionNode {
	public ExpressionNode $first { get; }
	public ExpressionNode $second { get; }

}