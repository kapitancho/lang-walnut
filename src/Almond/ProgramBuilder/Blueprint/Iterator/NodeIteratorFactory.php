<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint\Iterator;

use Iterator;
use Walnut\Lang\Almond\AST\Blueprint\Node\Node;

/**
 * Factory for creating various node iterators.
 */
interface NodeIteratorFactory {
	/**
	 * Create an iterator over direct children of a node.
	 *
	 * @param Node $node
	 * @return Iterator<Node>
	 */
	public function children(Node $node): Iterator;

	/**
	 * Create a recursive iterator over an entire node tree.
	 *
	 * @param Node $rootNode
	 * @return Iterator<Node>
	 */
	public function all(Node $rootNode): Iterator;

	/**
	 * Create a recursive iterator over an entire node tree.
	 *
	 * @param Node $rootNode
	 * @return NodeIterator
	 */
	public function recursive(Node $rootNode): NodeIterator;

	/**
	 * Filter nodes by instance type.
	 *
	 * @template T of Node
	 * @param NodeIterator $source
	 * @param class-string<T> $nodeClass
	 * @return Iterator<Node>
	 */
	public function filterByType(NodeIterator $source, string $nodeClass): Iterator;

	/**
	 * Filter nodes by instance type.
	 *
	 * @template T of Node
	 * @param NodeIterator $source
	 * @param list<class-string<T>> $nodeClasses
	 * @return Iterator<Node>
	 */
	public function filterByTypes(NodeIterator $source, array $nodeClasses): Iterator;

	/**
	 * Filter nodes by custom predicate.
	 *
	 * @param NodeIterator $source
	 * @param callable(Node): bool $predicate
	 * @return Iterator<Node>
	 */
	public function filter(NodeIterator $source, callable $predicate): Iterator;

	/**
	 * Find all nodes of a specific type in a tree (recursive).
	 *
	 * @template T of Node
	 * @param Node $rootNode
	 * @param class-string<T> $nodeClass
	 * @return Iterator<Node>
	 */
	public function findAllByType(Node $rootNode, string $nodeClass): Iterator;

	/**
	 * Find all nodes in a tree matching a predicate (recursive).
	 *
	 * @param Node $rootNode
	 * @param callable(Node): bool $predicate
	 * @return Iterator<Node>
	 */
	public function findAll(Node $rootNode, callable $predicate): Iterator;
}
