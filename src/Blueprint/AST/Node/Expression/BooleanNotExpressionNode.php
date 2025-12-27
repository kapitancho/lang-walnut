<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

interface BooleanNotExpressionNode extends ExpressionNode {
	public ExpressionNode $expression { get; }

}