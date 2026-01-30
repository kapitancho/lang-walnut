<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\UnionTypeNode as UnionTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

final readonly class UnionTypeNode implements UnionTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $left,
		public TypeNode $right,
	) {}

	public function children(): iterable {
		yield $this->left;
		yield $this->right;
	}

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