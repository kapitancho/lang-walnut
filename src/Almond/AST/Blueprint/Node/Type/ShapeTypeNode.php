<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

interface ShapeTypeNode extends TypeNode {
	public TypeNode $refType { get; }
}