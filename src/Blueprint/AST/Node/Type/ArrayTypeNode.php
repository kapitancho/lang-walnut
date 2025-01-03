<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

use BcMath\Number;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;

interface ArrayTypeNode extends TypeNode {
	public TypeNode $itemType { get; }
	public Number $minLength { get; }
	public Number|PlusInfinity $maxLength { get; }
}