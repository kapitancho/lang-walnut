<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

use BcMath\Number;
use Walnut\Lang\Almond\AST\Blueprint\Number\MinusInfinity;
use Walnut\Lang\Almond\AST\Blueprint\Number\PlusInfinity;

interface RealTypeNode extends TypeNode {
	public Number|MinusInfinity $minValue { get; }
	public Number|PlusInfinity $maxValue { get; }
}