<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

use BcMath\Number;
use Walnut\Lang\Almond\AST\Blueprint\Number\PlusInfinity;

interface BytesTypeNode extends TypeNode {
	public Number $minLength { get; }
	public Number|PlusInfinity $maxLength { get; }
}