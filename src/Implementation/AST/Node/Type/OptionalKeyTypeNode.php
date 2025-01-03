<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\OptionalKeyTypeNode as OptionalKeyTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;

final readonly class OptionalKeyTypeNode implements OptionalKeyTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $valueType
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'OptionalKeyType',
			'valueType' => $this->valueType
		];
	}
}