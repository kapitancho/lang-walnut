<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

interface OptionalKeyTypeNode extends TypeNode {
	public TypeNode $valueType { get; }
}