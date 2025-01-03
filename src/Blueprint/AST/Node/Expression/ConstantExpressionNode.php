<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Value\ValueNode;

interface ConstantExpressionNode extends ExpressionNode {
	public ValueNode $value { get; }
}