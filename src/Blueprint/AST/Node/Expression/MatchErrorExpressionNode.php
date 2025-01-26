<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

interface MatchErrorExpressionNode extends ExpressionNode {
	public ExpressionNode $condition { get; }
	public ExpressionNode $onError { get; }
	public ExpressionNode|null $else { get; }
}