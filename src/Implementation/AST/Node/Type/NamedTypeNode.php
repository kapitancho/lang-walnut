<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\NamedTypeNode as NamedTypeNodeInterface;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;

final readonly class NamedTypeNode implements NamedTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameIdentifier $name
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'NamedType',
			'name' => $this->name
		];
	}
}