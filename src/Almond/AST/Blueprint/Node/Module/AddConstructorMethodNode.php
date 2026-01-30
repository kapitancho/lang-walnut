<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Module;

use Walnut\Lang\Almond\AST\Blueprint\Node\FunctionBodyNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\NameAndTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

interface AddConstructorMethodNode extends ModuleDefinitionNode {
	public TypeNameNode $typeName { get; }
	public NameAndTypeNode $parameter { get; }
	public NameAndTypeNode $dependency { get; }
	public TypeNode $errorType { get; }
	public FunctionBodyNode $functionBody { get; }
}