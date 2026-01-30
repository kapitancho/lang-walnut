<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Iterator;

use Iterator;
use Walnut\Lang\Almond\AST\Blueprint\Node\Node;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Iterator\NodeIteratorFactory as NodeIteratorFactoryInterface;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Iterator\NodeIterator as NodeIteratorInterface;

/**
 * Factory for creating various node iterators.
 */
final readonly class NodeIteratorFactory implements NodeIteratorFactoryInterface {
	/**
	 * Create an iterator over direct children of a node.
	 *
	 * @param Node $node
	 * @return Iterator<Node>
	 */
	public function children(Node $node): Iterator {
		return new NodeIterator($node)->getIterator();
	}

	/**
	 * Create a recursive iterator over an entire node tree.
	 *
	 * @param Node $rootNode
	 * @return Iterator<Node>
	 */
	public function all(Node $rootNode): Iterator {
		return new RecursiveNodeIterator($rootNode)->getIterator();
	}

	/**
	 * Create a recursive iterator over an entire node tree.
	 *
	 * @param Node $rootNode
	 * @return NodeIterator
	 */
	public function recursive(Node $rootNode): NodeIteratorInterface {
		return new RecursiveNodeIterator($rootNode);
	}

	/**
	 * Filter nodes by instance type.
	 *
	 * @template T of Node
	 * @param NodeIteratorInterface $source
	 * @param class-string<T> $nodeClass
	 * @return Iterator<Node>
	 */
	public function filterByType(NodeIteratorInterface $source, string $nodeClass): Iterator {
		return new FilteredNodeIterator(
			$source,
			static fn(Node $node): bool => $node instanceof $nodeClass
		)->getIterator();
	}
	/**
	 * Filter nodes by instance type.
	 *
	 * @template T of Node
	 * @param NodeIterator $source
	 * @param list<class-string<T>> $nodeClasses
	 * @return Iterator<Node>
	 */
	public function filterByTypes(NodeIteratorInterface $source, array $nodeClasses): Iterator {
		return new FilteredNodeIterator(
			$source,
			static fn(Node $node): bool => array_any(
				$nodeClasses,
				fn(string $nodeClass): bool => $node instanceof $nodeClass
			)
		)->getIterator();
	}

	/**
	 * Filter nodes by custom predicate.
	 *
	 * @param NodeIteratorInterface $source
	 * @param callable(Node): bool $predicate
	 * @return Iterator<Node>
	 */
	public function filter(NodeIteratorInterface $source, callable $predicate): Iterator {
		return new FilteredNodeIterator($source, $predicate)->getIterator();
	}

	/**
	 * Find all nodes of a specific type in a tree (recursive).
	 *
	 * @template T of Node
	 * @param Node $rootNode
	 * @param class-string<T> $nodeClass
	 * @return Iterator<Node>
	 */
	public function findAllByType(Node $rootNode, string $nodeClass): Iterator {
		return $this->filterByType(
			$this->recursive($rootNode),
			$nodeClass
		);
	}

	/**
	 * Find all nodes in a tree matching a predicate (recursive).
	 *
	 * @param Node $rootNode
	 * @param callable(Node): bool $predicate
	 * @return Iterator<Node>
	 */
	public function findAll(Node $rootNode, callable $predicate): Iterator {
		return $this->filter(
			$this->recursive($rootNode),
			$predicate
		);
	}
}
