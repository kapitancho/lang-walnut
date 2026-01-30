<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

interface UnionTypeNode extends TypeNode {
	public TypeNode $left { get; }
	public TypeNode $right { get; }
}