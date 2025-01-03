<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

use BcMath\Number;
use Walnut\Lang\Blueprint\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Range\PlusInfinity;

interface RealTypeNode extends TypeNode {
	public Number|MinusInfinity $minValue { get; }
	public Number|PlusInfinity $maxValue { get; }
}