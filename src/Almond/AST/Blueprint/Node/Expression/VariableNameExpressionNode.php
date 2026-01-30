<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\VariableNameNode;

interface VariableNameExpressionNode extends ExpressionNode {
	public VariableNameNode $variableName { get; }
}