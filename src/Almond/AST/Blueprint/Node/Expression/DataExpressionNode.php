<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;

interface DataExpressionNode extends ExpressionNode {
	public TypeNameNode $typeName { get; }
	public ExpressionNode $value { get; }
}