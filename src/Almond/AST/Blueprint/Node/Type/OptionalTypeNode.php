<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

interface OptionalTypeNode extends TypeNode {
	public TypeNode $valueType { get; }
}