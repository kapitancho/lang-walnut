<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface SetExpressionNode extends ExpressionNode {
	/** @var ExpressionNode[] */
	public array $values { get; }
}