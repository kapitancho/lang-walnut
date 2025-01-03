<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;


use Walnut\Lang\Blueprint\AST\Node\Value\RealValueNode;

interface RealSubsetTypeNode extends TypeNode {
	/** @var list<RealValueNode> */
	public array $values { get; }
}