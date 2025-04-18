<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

interface TupleExpressionNode extends ExpressionNode {
	/** @var ExpressionNode[] */
	public array $values { get; }
}