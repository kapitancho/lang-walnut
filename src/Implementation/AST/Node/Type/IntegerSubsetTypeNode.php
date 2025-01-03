<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\IntegerSubsetTypeNode as IntegerSubsetTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Value\IntegerValueNode;

final readonly class IntegerSubsetTypeNode implements IntegerSubsetTypeNodeInterface {
	/** @param list<IntegerValueNode> $values */
	public function __construct(
		public SourceLocation $sourceLocation,
		public array $values
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'IntegerSubsetType',
			'values' => $this->values
		];
	}
}