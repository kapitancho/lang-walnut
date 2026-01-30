<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\ShapeTypeNode as ShapeTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

final readonly class ShapeTypeNode implements ShapeTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $refType
	) {}

	public function children(): iterable {
		yield $this->refType;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'ShapeType',
			'refType' => $this->refType
		];
	}
}