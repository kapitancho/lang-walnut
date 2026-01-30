<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Module;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;

interface AddTypeNode extends ModuleDefinitionNode {
	public TypeNameNode $name { get; }
}