<?php

namespace Walnut\Lang\Blueprint\AST\Node\Module;

use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;

interface AddAtomTypeNode extends ModuleDefinitionNode {
	public TypeNameIdentifier $name { get; }
}