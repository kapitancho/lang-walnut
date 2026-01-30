<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\StringSubsetTypeNode as StringSubsetTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\StringValueNode;

final readonly class StringSubsetTypeNode implements StringSubsetTypeNodeInterface {
	/** @param list<StringValueNode> $values */
	public function __construct(
		public SourceLocation $sourceLocation,
		public array $values
	) {}

	public function children(): iterable {
		return [];
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'StringSubsetType',
			'values' => $this->values
		];
	}
}