<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Value;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;

interface AtomValueNode extends ValueNode {
	public TypeNameNode $name { get; }
}