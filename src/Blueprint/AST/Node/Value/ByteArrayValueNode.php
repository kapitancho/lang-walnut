<?php

namespace Walnut\Lang\Blueprint\AST\Node\Value;

interface ByteArrayValueNode extends ValueNode {
	public string $value { get; }
}