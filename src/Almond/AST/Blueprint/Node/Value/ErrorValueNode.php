<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Value;

interface ErrorValueNode extends ValueNode {
	public ValueNode $value { get; }
}