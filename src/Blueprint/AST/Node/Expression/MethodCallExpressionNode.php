<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

use Walnut\Lang\Blueprint\Identifier\MethodNameIdentifier;

interface MethodCallExpressionNode extends ExpressionNode {
	public ExpressionNode $target { get; }
	public MethodNameIdentifier $methodName { get; }
	public ExpressionNode $parameter { get; }
}