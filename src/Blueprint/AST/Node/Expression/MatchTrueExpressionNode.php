<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

interface MatchTrueExpressionNode extends ExpressionNode {
	/** @var list<MatchExpressionPairNode|MatchExpressionDefaultNode> */
	public array $pairs { get; }
}