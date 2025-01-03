<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

interface UnionTypeNode extends TypeNode {
	public TypeNode $left { get; }
	public TypeNode $right { get; }
}