<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

interface FunctionTypeNode extends TypeNode {
	public TypeNode $parameterType { get; }
	public TypeNode $returnType { get; }
}