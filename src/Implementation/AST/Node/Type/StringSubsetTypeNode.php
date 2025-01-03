<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\StringSubsetTypeNode as StringSubsetTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Value\StringValueNode;

final readonly class StringSubsetTypeNode implements StringSubsetTypeNodeInterface {
	/** @param list<StringValueNode> $values */
	public function __construct(
		public SourceLocation $sourceLocation,
		public array $values
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'StringSubsetType',
			'values' => $this->values
		];
	}
}