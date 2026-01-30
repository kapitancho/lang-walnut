<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\MethodNameNode;

interface MethodCallExpressionNode extends ExpressionNode {
	public ExpressionNode $target { get; }
	public MethodNameNode $methodName { get; }
	public ExpressionNode $parameter { get; }
}