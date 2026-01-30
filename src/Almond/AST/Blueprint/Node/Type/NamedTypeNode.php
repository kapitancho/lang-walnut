<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;

interface NamedTypeNode extends TypeNode {
	public TypeNameNode $name { get; }
}