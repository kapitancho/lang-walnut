<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Value;

interface StringValueNode extends ValueNode {
	public string $value { get; }
}