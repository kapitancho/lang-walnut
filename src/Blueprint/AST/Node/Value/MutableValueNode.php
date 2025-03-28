<?php

namespace Walnut\Lang\Blueprint\AST\Node\Value;

use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;

interface MutableValueNode extends ValueNode {
	public TypeNode $type { get; }
	public ValueNode $value { get; }
}