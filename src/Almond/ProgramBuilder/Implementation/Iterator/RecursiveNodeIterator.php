<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Iterator;

use Generator;
use Walnut\Lang\Almond\AST\Blueprint\Node\Node;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Iterator\NodeIterator as NodeIteratorInterface;

/**
 * Lazy recursive depth-first iterator over an entire Node tree.
 * Yields the root node first, then recursively yields all descendants.
 */
final readonly class RecursiveNodeIterator implements NodeIteratorInterface {
	public function __construct(
		private Node $rootNode
	) {}

	/**
	 * Returns a generator that yields all nodes in the tree (depth-first).
	 *
	 * @return Generator<int, Node>
	 */
	public function getIterator(): Generator {
		yield from $this->traverse($this->rootNode);
	}

	/**
	 * @return Generator<int, Node>
	 */
	private function traverse(Node $node): Generator {
		yield $node;
		foreach ($node->children() as $child) {
			yield from $this->traverse($child);
		}
	}
}
