<?php

namespace Walnut\Lang\Blueprint\AST\Node\Type;

use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;

interface NumberIntervalNode {
	public MinusInfinity|NumberIntervalEndpoint $start { get; }
	public PlusInfinity|NumberIntervalEndpoint $end { get; }

}