<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

interface MutableExpressionNode extends ExpressionNode {
	public TypeNode $type { get; }
	public ExpressionNode $value { get; }
}