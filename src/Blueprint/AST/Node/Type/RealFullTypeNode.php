<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

interface RealFullTypeNode extends TypeNode {
	/** @var NumberIntervalNode[] */
	public array $intervals { get; }
}