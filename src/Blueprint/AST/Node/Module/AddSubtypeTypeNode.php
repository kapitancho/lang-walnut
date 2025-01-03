<?php

namespace Walnut\Lang\Blueprint\AST\Node\Module;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

interface AddSubtypeTypeNode extends ModuleDefinitionNode {
	public TypeNameIdentifier $name { get; }
	public TypeNode $baseType { get; }
	public ExpressionNode $constructorBody { get; }
	public TypeNode|null $errorType { get; }
}