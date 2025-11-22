<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

use BcMath\Number;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;

interface MapTypeNode extends TypeNode {
	public TypeNode $keyType { get; }
	public TypeNode $itemType { get; }
	public Number $minLength { get; }
	public Number|PlusInfinity $maxLength { get; }
}