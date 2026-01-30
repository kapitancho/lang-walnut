<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Module;

use Walnut\Lang\Almond\AST\Blueprint\Node\FunctionBodyNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

interface AddSealedTypeNode extends AddCompositeNamedTypeNode {
	public FunctionBodyNode|null $constructorBody { get; }
	public TypeNode|null $errorType { get; }
}