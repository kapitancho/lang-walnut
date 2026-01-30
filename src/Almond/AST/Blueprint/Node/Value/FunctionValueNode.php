<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Value;

use Walnut\Lang\Almond\AST\Blueprint\Node\FunctionBodyNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\NameAndTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

interface FunctionValueNode extends ValueNode {
	public NameAndTypeNode $parameter { get; }
	public NameAndTypeNode $dependency { get; }
	public TypeNode $returnType { get; }
	public FunctionBodyNode $functionBody { get; }
}