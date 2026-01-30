<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

interface IntegerFullTypeNode extends TypeNode {
	/** @var NumberIntervalNode[] */
	public array $intervals { get; }
}