<?php

namespace Walnut\Lang\Blueprint\AST\Node\Value;

interface TupleValueNode extends ValueNode {
	/** @var list<ValueNode> */
	public array $values { get; }
}