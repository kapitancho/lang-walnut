<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface TupleExpressionNode extends ExpressionNode {
	/** @var ExpressionNode[] */
	public array $values { get; }
}