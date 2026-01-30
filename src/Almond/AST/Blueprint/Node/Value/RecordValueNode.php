<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Value;

interface RecordValueNode extends ValueNode {
	/** @var array<string, ValueNode> */
	public array $values { get; }
}