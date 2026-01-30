<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface PropertyAccessExpressionNode extends ExpressionNode {
	public ExpressionNode $target { get; }
	public int|string $propertyName { get; }
}