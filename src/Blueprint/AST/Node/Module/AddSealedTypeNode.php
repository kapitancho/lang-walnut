<?php

namespace Walnut\Lang\Blueprint\AST\Node\Module;

use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\AST\Node\Type\RecordTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

interface AddSealedTypeNode extends ModuleDefinitionNode {
	public TypeNameIdentifier $name { get; }
	public RecordTypeNode $valueType { get; }
	public FunctionBodyNode $constructorBody { get; }
	public TypeNode|null $errorType { get; }
}