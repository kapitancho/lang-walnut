<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\MutableTypeNode as MutableTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;

final readonly class MutableTypeNode implements MutableTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $valueType
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'MutableType',
			'valueType' => $this->valueType
		];
	}
}