<?php

namespace Walnut\Lang\Blueprint\AST\Node\Value;

interface ErrorValueNode extends ValueNode {
	public ValueNode $value { get; }
}