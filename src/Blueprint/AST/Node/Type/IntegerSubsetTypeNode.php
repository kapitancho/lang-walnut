<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\Value\IntegerValueNode;

interface IntegerSubsetTypeNode extends TypeNode {
	/** @var list<IntegerValueNode> */
	public array $values { get; }
}