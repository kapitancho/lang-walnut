<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\UnionTypeNode as UnionTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;

final readonly class UnionTypeNode implements UnionTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $left,
		public TypeNode $right,
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'UnionType',
			'left' => $this->left,
			'right' => $this->right,
		];
	}
}