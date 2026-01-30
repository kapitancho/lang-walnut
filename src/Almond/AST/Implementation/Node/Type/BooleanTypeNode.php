<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\BooleanTypeNode as BooleanTypeNodeInterface;

final readonly class BooleanTypeNode implements BooleanTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation
	) {}

	public function children(): iterable {
		return [];
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'BooleanType',
		];
	}
}