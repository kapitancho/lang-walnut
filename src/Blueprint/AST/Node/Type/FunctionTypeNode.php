<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

interface FunctionTypeNode extends TypeNode {
	public TypeNode $parameterType { get; }
	public TypeNode $returnType { get; }
}