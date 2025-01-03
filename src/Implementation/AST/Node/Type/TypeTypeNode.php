<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeTypeNode as TypeTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;

final readonly class TypeTypeNode implements TypeTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $refType
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'TypeType',
			'refType' => $this->refType
		];
	}
}