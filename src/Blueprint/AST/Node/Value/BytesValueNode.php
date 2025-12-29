<?php

namespace Walnut\Lang\Blueprint\AST\Node\Value;

interface BytesValueNode extends ValueNode {
	public string $value { get; }
}