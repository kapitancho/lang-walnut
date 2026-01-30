<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Module;

use Walnut\Lang\Almond\AST\Blueprint\Node\FunctionBodyNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\MethodNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\NameAndTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

interface AddMethodNode extends ModuleDefinitionNode {
	public TypeNameNode $targetType { get; }
	public MethodNameNode $methodName { get; }
	public NameAndTypeNode $parameter { get; }
	public NameAndTypeNode $dependency { get; }
	public TypeNode $returnType { get; }
	public FunctionBodyNode $functionBody { get; }
}