<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\NothingTypeNode as NothingTypeNodeInterface;

final readonly class NothingTypeNode implements NothingTypeNodeInterface {
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
			'nodeName' => 'NothingType',
		];
	}
}