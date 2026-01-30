<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\RecordTypeNode as RecordTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

final readonly class RecordTypeNode implements RecordTypeNodeInterface {
	/** @param array<string, TypeNode> $types */
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
			'nodeName' => 'RecordType',
			'types' => $this->types,
			'restType' => $this->restType
		];
	}
}