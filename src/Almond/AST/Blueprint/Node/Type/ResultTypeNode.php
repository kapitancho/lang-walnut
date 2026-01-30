<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

interface ResultTypeNode extends TypeNode {
	public TypeNode $returnType { get; }
	public TypeNode $errorType { get; }
}