<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\StringSubsetTypeNode as StringSubsetTypeNodeInterface;

final readonly class StringSubsetTypeNode implements StringSubsetTypeNodeInterface {
	/** @param list<string> $values */
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