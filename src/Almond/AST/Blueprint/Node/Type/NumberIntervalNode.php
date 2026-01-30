<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceNode;
use Walnut\Lang\Almond\AST\Blueprint\Number\MinusInfinity;
use Walnut\Lang\Almond\AST\Blueprint\Number\PlusInfinity;

interface NumberIntervalNode extends SourceNode {
	public MinusInfinity|NumberIntervalEndpointNode $start { get; }
	public PlusInfinity|NumberIntervalEndpointNode $end { get; }
}