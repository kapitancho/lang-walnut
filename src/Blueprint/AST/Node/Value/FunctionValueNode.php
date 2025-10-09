<?php

namespace Walnut\Lang\Blueprint\AST\Node\Value;

use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\AST\Node\NameAndTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;

interface FunctionValueNode extends ValueNode {
	public NameAndTypeNode $parameter { get; }
	public NameAndTypeNode $dependency { get; }
	public TypeNode $returnType { get; }
	public FunctionBodyNode $functionBody { get; }
}