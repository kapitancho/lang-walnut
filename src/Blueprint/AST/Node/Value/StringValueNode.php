<?php

namespace Walnut\Lang\Blueprint\AST\Node\Value;

interface StringValueNode extends ValueNode {
	public string $value { get; }
}