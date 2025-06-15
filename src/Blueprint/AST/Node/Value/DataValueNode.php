<?php

namespace Walnut\Lang\Blueprint\AST\Node\Value;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

interface DataValueNode extends ValueNode {
	public TypeNameIdentifier $name { get; }
	public ValueNode $value { get; }
}