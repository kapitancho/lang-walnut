<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Value;

interface ValueValueNode extends ValueNode {
	public ValueNode $value { get; }
}