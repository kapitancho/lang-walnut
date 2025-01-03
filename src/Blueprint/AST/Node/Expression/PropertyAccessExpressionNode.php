<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

interface PropertyAccessExpressionNode extends ExpressionNode {
	public ExpressionNode $target { get; }
	public int|string $propertyName { get; }
}