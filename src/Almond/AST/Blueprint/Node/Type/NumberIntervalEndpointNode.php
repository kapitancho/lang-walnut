<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

use BcMath\Number;
use Walnut\Lang\Almond\AST\Blueprint\Node\Node;

interface NumberIntervalEndpointNode extends Node {
	public Number $value { get; }
	public bool $inclusive { get; }
}