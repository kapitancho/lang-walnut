<?php

namespace Walnut\Lang\Blueprint\Function;

use Stringable;
use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;

interface FunctionBodyDraft extends Stringable {
	public ExpressionNode $expressionNode { get; }
}