<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

interface RecordExpressionNode extends ExpressionNode {
	/** @var array<string, ExpressionNode> */
	public array $values { get; }
}