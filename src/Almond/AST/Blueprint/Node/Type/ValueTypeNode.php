<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

interface ValueTypeNode extends TypeNode {
	public TypeNode $valueType { get; }
}