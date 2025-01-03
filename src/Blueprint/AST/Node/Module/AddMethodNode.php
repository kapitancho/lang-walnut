<?php

namespace Walnut\Lang\Blueprint\AST\Node\Module;

use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;

interface AddMethodNode extends ModuleDefinitionNode {
	public TypeNode $targetType { get; }
	public MethodNameIdentifier $methodName { get; }
	public TypeNode $parameterType { get; }
	public TypeNode $dependencyType { get; }
	public TypeNode $returnType { get; }
	public FunctionBodyNode $functionBody { get; }
}