<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface SequenceExpressionNode extends ExpressionNode {
	/** @var ExpressionNode[] */
	public array $expressions { get; }
}