<?php

namespace Walnut\Lang\Blueprint\AST\Node\Value;

use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

interface EnumerationValueNode extends ValueNode {
	public TypeNameIdentifier $name { get; }
	public EnumValueIdentifier $enumValue { get; }
}