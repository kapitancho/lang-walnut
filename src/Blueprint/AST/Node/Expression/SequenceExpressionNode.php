<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

interface SequenceExpressionNode extends ExpressionNode {
	/** @var ExpressionNode[] */
	public array $expressions { get; }
}