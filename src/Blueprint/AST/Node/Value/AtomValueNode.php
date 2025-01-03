<?php

namespace Walnut\Lang\Blueprint\AST\Node\Value;

use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;

interface AtomValueNode extends ValueNode {
	public TypeNameIdentifier $name { get; }
}