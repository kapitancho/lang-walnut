<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

use BcMath\Number;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;

interface RealTypeNode extends TypeNode {
	public Number|MinusInfinity $minValue { get; }
	public Number|PlusInfinity $maxValue { get; }
}