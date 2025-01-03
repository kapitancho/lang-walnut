<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\IntersectionTypeNode as IntersectionTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;

final readonly class IntersectionTypeNode implements IntersectionTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $left,
		public TypeNode $right,
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'IntersectionType',
			'left' => $this->left,
			'right' => $this->right,
		];
	}
}