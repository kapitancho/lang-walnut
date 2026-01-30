<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface MatchTypeExpressionNode extends ExpressionNode {
	public ExpressionNode $target { get; }
	/** @var list<MatchExpressionPairNode> */
	public array $pairs { get; }
	public MatchExpressionDefaultNode|null $default { get; }

}