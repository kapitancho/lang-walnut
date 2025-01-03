<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;

interface MutableExpressionNode extends ExpressionNode {
	public TypeNode $type { get; }
	public ExpressionNode $value { get; }
}