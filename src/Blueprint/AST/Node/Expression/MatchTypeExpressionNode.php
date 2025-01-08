<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

interface MatchTypeExpressionNode extends ExpressionNode {
	public ExpressionNode $target { get; }
	/** @var list<MatchExpressionPairNode|MatchExpressionDefaultNode> */
	public array $pairs { get; }
}