<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface MatchTrueExpressionNode extends ExpressionNode {
	/** @var list<MatchExpressionPairNode> */
	public array $pairs { get; }
	public MatchExpressionDefaultNode|null $default { get; }

}