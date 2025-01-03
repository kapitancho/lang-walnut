<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\TupleTypeNode as TupleTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;

final readonly class TupleTypeNode implements TupleTypeNodeInterface {
	/** @param list<TypeNode> $types */
	public function __construct(
		public SourceLocation $sourceLocation,
		public array $types,
		public TypeNode $restType
	) {}

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