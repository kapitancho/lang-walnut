<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\EnumerationValueNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;

interface EnumerationSubsetTypeNode extends TypeNode {
	public TypeNameNode $name { get; }
	/** @var list<EnumerationValueNameNode> $values */
	public array $values { get; }
}