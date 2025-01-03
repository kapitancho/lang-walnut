<?php

namespace Walnut\Lang\Blueprint\AST\Node\Value;

interface RecordValueNode extends ValueNode {
	/** @var array<string, ValueNode> */
	public array $values { get; }
}