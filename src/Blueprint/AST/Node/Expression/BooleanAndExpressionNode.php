<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

interface BooleanAndExpressionNode extends ExpressionNode {
	public ExpressionNode $first { get; }
	public ExpressionNode $second { get; }

}