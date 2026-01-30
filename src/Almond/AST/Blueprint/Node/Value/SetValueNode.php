<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Value;

interface SetValueNode extends ValueNode {
	/** @var list<ValueNode> */
	public array $values { get; }
}