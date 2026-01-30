<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

interface MatchErrorExpressionNode extends ExpressionNode {
	public ExpressionNode $condition { get; }
	public ExpressionNode $onError { get; }
	public ExpressionNode|null $else { get; }
}