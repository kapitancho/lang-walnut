<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TupleTypeNode as TupleTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

final readonly class TupleTypeNode implements TupleTypeNodeInterface {
	/** @param list<TypeNode> $types */
	public function __construct(
		public SourceLocation $sourceLocation,
		public array $types,
		public TypeNode $restType
	) {}

	public function children(): iterable {
		yield from $this->types;
		yield $this->restType;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'TupleType',
			'types' => $this->types,
			'restType' => $this->restType
		];
	}
}