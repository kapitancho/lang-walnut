<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Module;

use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

interface AddCompositeNamedTypeNode extends AddTypeNode {
	public TypeNode $valueType { get; }
}