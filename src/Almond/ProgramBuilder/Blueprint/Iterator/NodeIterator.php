<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint\Iterator;

use Generator;
use Walnut\Lang\Almond\AST\Blueprint\Node\Node;

interface NodeIterator {
	/**
	 * Returns a generator that yields nodes.
	 *
	 * @return Generator<int, Node>
	 */
	public function getIterator(): Generator;
}