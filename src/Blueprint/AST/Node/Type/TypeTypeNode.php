<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

interface TypeTypeNode extends TypeNode {
	public TypeNode $refType { get; }
}