<?php

namespace Walnut\Lang\Blueprint\AST\Node;

use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;

interface NameAndTypeNode extends SourceNode {
	public TypeNode $type { get; }
	public VariableNameIdentifier|null $name { get; }
}