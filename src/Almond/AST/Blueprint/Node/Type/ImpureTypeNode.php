<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

interface ImpureTypeNode extends TypeNode {
	public TypeNode $valueType { get; }
}