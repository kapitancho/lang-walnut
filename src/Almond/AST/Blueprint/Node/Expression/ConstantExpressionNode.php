<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Value\ValueNode;

interface ConstantExpressionNode extends ExpressionNode {
	public ValueNode $value { get; }
}