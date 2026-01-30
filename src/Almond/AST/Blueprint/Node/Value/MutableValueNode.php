<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Value;

use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

interface MutableValueNode extends ValueNode {
	public TypeNode $type { get; }
	public ValueNode $value { get; }
}