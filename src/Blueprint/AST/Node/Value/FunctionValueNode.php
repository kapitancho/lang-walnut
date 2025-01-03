<?php

namespace Walnut\Lang\Blueprint\AST\Node\Value;

use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;

interface FunctionValueNode extends ValueNode {
	public TypeNode $parameterType { get; }
	public TypeNode $dependencyType { get; }
	public TypeNode $returnType { get; }
	public FunctionBodyNode $functionBody { get; }
}