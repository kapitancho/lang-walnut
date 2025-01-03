<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

interface FunctionCallExpressionNode extends ExpressionNode {
	public ExpressionNode $target { get; }
	public ExpressionNode $parameter { get; }
}