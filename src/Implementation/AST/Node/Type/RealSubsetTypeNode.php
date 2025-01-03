<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\RealSubsetTypeNode as RealSubsetTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Value\RealValueNode;

final readonly class RealSubsetTypeNode implements RealSubsetTypeNodeInterface {
	/** @param list<RealValueNode> $values */
	public function __construct(
		public SourceLocation $sourceLocation,
		public array $values
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'RealSubsetType',
			'values' => $this->values
		];
	}
}