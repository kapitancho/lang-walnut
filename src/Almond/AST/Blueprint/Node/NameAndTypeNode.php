<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\VariableNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

interface NameAndTypeNode extends SourceNode {
	public TypeNode $type { get; }
	public VariableNameNode|null $name { get; }
}