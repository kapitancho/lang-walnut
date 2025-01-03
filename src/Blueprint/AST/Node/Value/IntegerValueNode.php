<?php

namespace Walnut\Lang\Blueprint\AST\Node\Value;

use bcmath\Number;

interface IntegerValueNode extends ValueNode {
	public Number $value { get; }
}