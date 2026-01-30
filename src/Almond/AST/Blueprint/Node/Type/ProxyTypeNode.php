<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;

interface ProxyTypeNode extends TypeNode {
	public TypeNameNode $name { get; }
}