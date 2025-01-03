<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

use Walnut\Lang\Blueprint\Type\MetaTypeValue;

interface MetaTypeTypeNode extends TypeNode {
	public MetaTypeValue $value { get; }
}