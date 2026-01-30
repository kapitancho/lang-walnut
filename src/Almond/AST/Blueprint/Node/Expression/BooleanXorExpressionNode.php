<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface BooleanXorExpressionNode extends ExpressionNode {
	public ExpressionNode $first { get; }
	public ExpressionNode $second { get; }

}