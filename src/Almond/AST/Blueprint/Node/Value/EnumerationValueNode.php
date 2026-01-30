<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Value;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\EnumerationValueNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;

interface EnumerationValueNode extends ValueNode {
	public TypeNameNode $name { get; }
	public EnumerationValueNameNode $enumValue { get; }
}