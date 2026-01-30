<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface BooleanNotExpressionNode extends ExpressionNode {
	public ExpressionNode $expression { get; }

}