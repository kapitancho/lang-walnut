<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

interface IntersectionTypeNode extends TypeNode {
	public TypeNode $left { get; }
	public TypeNode $right { get; }
}