<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\EnumerationValueNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\EnumerationSubsetTypeNode as EnumerationSubsetTypeNodeInterface;

final readonly class EnumerationSubsetTypeNode implements EnumerationSubsetTypeNodeInterface {
	/** @param list<EnumerationValueNameNode> $values */
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameNode $name,
		public array $values,
	) {}

	public function children(): iterable {
		yield $this->name;
		yield from $this->values;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'EnumerationSubsetType',
			'name' => $this->name,
			'values' => $this->values
		];
	}
}