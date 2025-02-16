<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

interface ShapeTypeNode extends TypeNode {
	public TypeNode $refType { get; }
}