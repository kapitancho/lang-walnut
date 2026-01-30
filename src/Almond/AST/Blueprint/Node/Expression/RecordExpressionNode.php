<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface RecordExpressionNode extends ExpressionNode {
	/** @var array<string, ExpressionNode> */
	public array $values { get; }
}