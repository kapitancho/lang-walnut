<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Module;

use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

interface AddAliasTypeNode extends AddTypeNode {
	public TypeNode $aliasedType { get; }
}