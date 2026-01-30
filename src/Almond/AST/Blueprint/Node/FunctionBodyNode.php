<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;

interface FunctionBodyNode extends SourceNode {
	public ExpressionNode $expression { get; }
}