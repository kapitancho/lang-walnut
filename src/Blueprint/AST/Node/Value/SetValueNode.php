<?php

namespace Walnut\Lang\Blueprint\AST\Node\Value;

interface SetValueNode extends ValueNode {
	/** @var list<ValueNode> */
	public array $values { get; }
}