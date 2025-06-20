<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

interface IntegerFullTypeNode extends TypeNode {
	/** @var NumberIntervalNode[] */
	public array $intervals { get; }
}