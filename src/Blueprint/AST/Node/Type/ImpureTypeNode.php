<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

interface ImpureTypeNode extends TypeNode {
	public TypeNode $valueType { get; }
}