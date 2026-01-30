<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface ReturnExpressionNode extends ExpressionNode {
	public ExpressionNode $returnedExpression { get; }
}