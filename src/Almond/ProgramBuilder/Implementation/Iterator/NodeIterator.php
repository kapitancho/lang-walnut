<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Iterator;

use Generator;
use Walnut\Lang\Almond\AST\Blueprint\Node\Node;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Iterator\NodeIterator as NodeIteratorInterface;

/**
 * Lazy iterator over direct children of a Node.
 */
final readonly class NodeIterator implements NodeIteratorInterface {
	public function __construct(
		private Node $node
	) {}

	/**
	 * Returns a generator that yields direct children of the node.
	 *
	 * @return Generator<int, Node>
	 */
	public function getIterator(): Generator {
		foreach ($this->node->children() as $child) {
			yield $child;
		}
	}
}
