<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

interface MatchIfExpressionNode extends ExpressionNode {
	public ExpressionNode $condition { get; }
	public ExpressionNode $then { get; }
	public ExpressionNode $else { get; }
}