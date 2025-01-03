<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Expression\MatchExpressionDefaultNode as MatchExpressionPairNode;

interface MatchTrueExpressionNode extends ExpressionNode {
	/** @var list<MatchExpressionPairNode|MatchExpressionDefaultNode> */
	public array $pairs { get; }
}