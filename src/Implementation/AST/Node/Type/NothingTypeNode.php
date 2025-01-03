<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\NothingTypeNode as NothingTypeNodeInterface;

final readonly class NothingTypeNode implements NothingTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'NothingType',
		];
	}
}