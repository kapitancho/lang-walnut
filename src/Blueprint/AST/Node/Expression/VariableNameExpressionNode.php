<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;

interface VariableNameExpressionNode extends ExpressionNode {
	public VariableNameIdentifier $variableName { get; }
}