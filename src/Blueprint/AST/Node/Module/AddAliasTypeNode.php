<?php

namespace Walnut\Lang\Blueprint\AST\Node\Module;

use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

interface AddAliasTypeNode extends ModuleDefinitionNode {
	public TypeNameIdentifier $name { get; }
	public TypeNode $aliasedType { get; }
}