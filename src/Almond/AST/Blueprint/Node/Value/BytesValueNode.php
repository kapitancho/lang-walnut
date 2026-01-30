<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Value;

interface BytesValueNode extends ValueNode {
	public string $value { get; }
}