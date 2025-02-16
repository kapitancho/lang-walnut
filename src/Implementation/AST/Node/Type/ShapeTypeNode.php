<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\ShapeTypeNode as ShapeTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;

final readonly class ShapeTypeNode implements ShapeTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $refType
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'ShapeType',
			'refType' => $this->refType
		];
	}
}