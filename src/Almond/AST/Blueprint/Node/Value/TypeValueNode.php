<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Value;

use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

interface TypeValueNode extends ValueNode {
	public TypeNode $type { get; }
}