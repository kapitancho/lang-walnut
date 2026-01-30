<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\Value\StringValueNode;

interface StringSubsetTypeNode extends TypeNode {
	/** @var list<StringValueNode> */
	public array $values { get; }
}