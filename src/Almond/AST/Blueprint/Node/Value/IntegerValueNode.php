<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Value;

use bcmath\Number;

interface IntegerValueNode extends ValueNode {
	public Number $value { get; }
}