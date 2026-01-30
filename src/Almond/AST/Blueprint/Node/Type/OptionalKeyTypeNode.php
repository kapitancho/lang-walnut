<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

interface OptionalKeyTypeNode extends TypeNode {
	public TypeNode $valueType { get; }
}