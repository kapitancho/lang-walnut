<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

interface MatchValueExpressionNode extends ExpressionNode {
	public ExpressionNode $target { get; }
	/** @var list<MatchExpressionPairNode|MatchExpressionDefaultNode> */
	public array $pairs { get; }
}