<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

interface ResultTypeNode extends TypeNode {
	public TypeNode $returnType { get; }
	public TypeNode $errorType { get; }
}