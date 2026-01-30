<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface FunctionCallExpressionNode extends ExpressionNode {
	public ExpressionNode $target { get; }
	public ExpressionNode $parameter { get; }
}