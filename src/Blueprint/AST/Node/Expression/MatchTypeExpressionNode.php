<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Expression\MatchExpressionDefaultNode as MatchExpressionPairNode;

interface MatchTypeExpressionNode extends ExpressionNode {
	public ExpressionNode $target { get; }
	/** @var list<MatchExpressionPairNode|MatchExpressionDefaultNode> */
	public array $pairs { get; }
}