<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

interface MutableTypeNode extends TypeNode {
	public TypeNode $valueType { get; }
}