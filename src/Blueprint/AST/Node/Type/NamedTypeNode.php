<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

interface NamedTypeNode extends TypeNode {
	public TypeNameIdentifier $name { get; }
}