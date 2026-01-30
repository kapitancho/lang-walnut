<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Value;

interface TupleValueNode extends ValueNode {
	/** @var list<ValueNode> */
	public array $values { get; }
}