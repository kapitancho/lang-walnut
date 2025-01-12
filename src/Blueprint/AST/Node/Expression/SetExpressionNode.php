<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

interface SetExpressionNode extends ExpressionNode {
	/** @var ExpressionNode[] */
	public array $values { get; }
}