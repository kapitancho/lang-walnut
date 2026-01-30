<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface MatchIfExpressionNode extends ExpressionNode {
	public ExpressionNode $condition { get; }
	public ExpressionNode $then { get; }
	public ExpressionNode $else { get; }
}