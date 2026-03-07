<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

use BcMath\Number;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\IntegerValueNode;

interface IntegerSubsetTypeNode extends TypeNode {
	/** @var list<IntegerValueNode> */
	public array $values { get; }
}