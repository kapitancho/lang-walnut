<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node;

use JsonSerializable;

interface Node extends JsonSerializable {
	/** @return iterable<Node> */
	public function children(): iterable;
}