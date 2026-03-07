<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\RealSubsetTypeNode as RealSubsetTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\RealValueNode;

final readonly class RealSubsetTypeNode implements RealSubsetTypeNodeInterface {
	/** @param list<RealValueNode> $values */
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
			'nodeName' => 'RealSubsetType',
			'values' => $this->values
		];
	}
}