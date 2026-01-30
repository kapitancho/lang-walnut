<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Value;

use bcmath\Number;

interface RealValueNode extends ValueNode {
	public Number $value { get; }
}