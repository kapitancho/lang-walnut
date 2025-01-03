<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

use BcMath\Number;
use Walnut\Lang\Blueprint\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Range\PlusInfinity;

interface IntegerTypeNode extends TypeNode {
	public Number|MinusInfinity $minValue { get; }
	public Number|PlusInfinity $maxValue { get; }
}