<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;

interface ProxyTypeNode extends TypeNode {
	public TypeNameIdentifier $name { get; }
}