<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface EmptyAsErrorExpressionNode extends ExpressionNode {
	public ExpressionNode $targetExpression { get; }
	public ExpressionNode $errorExpression { get; }
}