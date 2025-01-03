<?php

namespace Walnut\Lang\Blueprint\AST\Node\Module;

use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;

interface AddConstructorMethodNode extends ModuleDefinitionNode {
	public TypeNameIdentifier $typeName { get; }
	public TypeNode $parameterType { get; }
	public TypeNode $dependencyType { get; }
	public TypeNode $errorType { get; }
	public FunctionBodyNode $functionBody { get; }
}