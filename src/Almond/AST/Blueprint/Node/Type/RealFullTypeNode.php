<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

interface RealFullTypeNode extends TypeNode {
	/** @var NumberIntervalNode[] */
	public array $intervals { get; }
}