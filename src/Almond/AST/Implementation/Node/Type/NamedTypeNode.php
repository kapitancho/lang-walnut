<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\NamedTypeNode as NamedTypeNodeInterface;

final readonly class NamedTypeNode implements NamedTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameNode $name
	) {}

	public function children(): iterable {
		yield $this->name;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'NamedType',
			'name' => $this->name
		];
	}
}