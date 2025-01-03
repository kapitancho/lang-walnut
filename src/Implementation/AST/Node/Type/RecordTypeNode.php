<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\RecordTypeNode as RecordTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;

final readonly class RecordTypeNode implements RecordTypeNodeInterface {
	/** @param array<string, TypeNode> $types */
	public function __construct(
		public SourceLocation $sourceLocation,
		public array $types,
		public TypeNode $restType
	) {}

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