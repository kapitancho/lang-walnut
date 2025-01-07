<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

use BcMath\Number;

interface IntegerSubsetTypeNode extends TypeNode {
	/** @var list<Number> */
	public array $values { get; }
}