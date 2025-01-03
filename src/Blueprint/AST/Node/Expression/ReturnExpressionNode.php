<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

interface ReturnExpressionNode extends ExpressionNode {
	public ExpressionNode $returnedExpression { get; }
}