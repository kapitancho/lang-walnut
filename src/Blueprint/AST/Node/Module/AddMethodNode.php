<?php

namespace Walnut\Lang\Blueprint\AST\Node\Module;

use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\AST\Node\NameAndTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;

interface AddMethodNode extends ModuleDefinitionNode {
	public TypeNode $targetType { get; }
	public MethodNameIdentifier $methodName { get; }
	public NameAndTypeNode $parameter { get; }
	public NameAndTypeNode $dependency { get; }
	public TypeNode $returnType { get; }
	public FunctionBodyNode $functionBody { get; }
}