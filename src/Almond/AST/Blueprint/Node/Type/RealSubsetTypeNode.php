<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;


use Walnut\Lang\Almond\AST\Blueprint\Node\Value\RealValueNode;

interface RealSubsetTypeNode extends TypeNode {
	/** @var list<RealValueNode> */
	public array $values { get; }
}