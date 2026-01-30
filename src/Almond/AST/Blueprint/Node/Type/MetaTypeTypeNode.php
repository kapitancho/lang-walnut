<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

interface MetaTypeTypeNode extends TypeNode {
	public string $value { get; }
}