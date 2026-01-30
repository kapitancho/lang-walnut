<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Iterator;

use Generator;
use Walnut\Lang\Almond\AST\Blueprint\Node\Node;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Iterator\NodeIterator as NodeIteratorInterface;

/**
 * Lazily filters nodes based on a predicate function.
 * Uses composition to wrap another NodeIterator.
 */
final readonly class FilteredNodeIterator implements NodeIteratorInterface {
	/**
	 * @param NodeIteratorInterface $source
	 * @param callable(Node): bool $predicate
	 */
	public function __construct(
		private NodeIteratorInterface $source,
		private mixed $predicate
	) {}

	/**
	 * Returns a generator that yields only nodes matching the predicate.
	 *
	 * @return Generator<int, Node>
	 */
	public function getIterator(): Generator {
		foreach ($this->source->getIterator() as $node) {
			if (($this->predicate)($node)) {
				yield $node;
			}
		}
	}

	/**
	 * Converts filtered results to array.
	 * Note: This will consume the entire source iterator.
	 *
	 * @return array<int, Node>
	 */
	public function toArray(): array {
		$result = [];
		foreach ($this->getIterator() as $node) {
			$result[] = $node;
		}
		return $result;
	}
}
