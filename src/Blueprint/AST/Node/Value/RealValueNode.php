<?php

namespace Walnut\Lang\Blueprint\AST\Node\Value;

use bcmath\Number;

interface RealValueNode extends ValueNode {
	public Number $value { get; }
}