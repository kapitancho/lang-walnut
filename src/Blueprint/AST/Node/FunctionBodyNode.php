<?php

namespace Walnut\Lang\Blueprint\AST\Node;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;

interface FunctionBodyNode extends SourceNode {
	public ExpressionNode $expression { get; }
}