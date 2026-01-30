<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

interface TypeTypeNode extends TypeNode {
	public TypeNode $refType { get; }
}