<?php

namespace Walnut\Lang\Blueprint\AST\Node\Module;

use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

interface AddUserTypeNode extends AddCompositeNamedTypeNode {
	public FunctionBodyNode|null $constructorBody { get; }
	public TypeNode|null $errorType { get; }
}