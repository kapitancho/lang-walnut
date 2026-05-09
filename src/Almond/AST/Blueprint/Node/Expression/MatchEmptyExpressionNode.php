<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface MatchEmptyExpressionNode extends ExpressionNode {
	public ExpressionNode $condition { get; }
	public ExpressionNode $onEmpty { get; }
	public ExpressionNode|null $else { get; }
}