<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\IntegerSubsetTypeNode as IntegerSubsetTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\IntegerValueNode;

final readonly class IntegerSubsetTypeNode implements IntegerSubsetTypeNodeInterface {
	/** @param list<IntegerValueNode> $values */
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
			'nodeName' => 'IntegerSubsetType',
			'values' => $this->values
		];
	}
}