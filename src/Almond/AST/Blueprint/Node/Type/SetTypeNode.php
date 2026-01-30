<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

use BcMath\Number;
use Walnut\Lang\Almond\AST\Blueprint\Number\PlusInfinity;

interface SetTypeNode extends TypeNode {
	public TypeNode $itemType { get; }
	public Number $minLength { get; }
	public Number|PlusInfinity $maxLength { get; }
}