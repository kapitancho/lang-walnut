<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

interface MutableTypeNode extends TypeNode {
	public TypeNode $valueType { get; }
}