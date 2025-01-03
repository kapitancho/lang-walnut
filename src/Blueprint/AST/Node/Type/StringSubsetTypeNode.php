<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\Value\StringValueNode;

interface StringSubsetTypeNode extends TypeNode {
	/** @var list<StringValueNode> */
	public array $values { get; }
}