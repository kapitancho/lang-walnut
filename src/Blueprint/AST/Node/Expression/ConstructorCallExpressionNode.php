<?php

namespace Walnut\Lang\Blueprint\AST\Node\Expression;

use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;

interface ConstructorCallExpressionNode extends ExpressionNode {
	public TypeNameIdentifier $typeName { get; }
	public ExpressionNode $parameter { get; }
}