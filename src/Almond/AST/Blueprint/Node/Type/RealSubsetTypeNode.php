<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;


use BcMath\Number;

interface RealSubsetTypeNode extends TypeNode {
	/** @var list<Number> */
	public array $values { get; }
}