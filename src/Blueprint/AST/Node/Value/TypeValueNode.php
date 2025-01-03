<?php

namespace Walnut\Lang\Blueprint\AST\Node\Value;

use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;

interface TypeValueNode extends ValueNode {
	public TypeNode $type { get; }
}