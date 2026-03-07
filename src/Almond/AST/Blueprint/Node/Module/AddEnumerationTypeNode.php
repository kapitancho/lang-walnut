<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Module;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\EnumerationValueNameNode;

interface AddEnumerationTypeNode extends AddTypeNode {
	/** @var list<EnumerationValueNameNode> $values */
	public array $values { get; }
}