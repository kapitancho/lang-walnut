<?php

namespace Walnut\Lang\Blueprint\AST\Node\Value;

use Walnut\Lang\Blueprint\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;

interface EnumerationValueNode extends ValueNode {
	public TypeNameIdentifier $name { get; }
	public EnumValueIdentifier $enumValue { get; }
}