<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Name;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode as TypeNameNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Node;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class TypeNameNode implements TypeNameNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public string $name
	) {}

	public function equals(TypeNameNodeInterface $other): bool {
		return $this->name === $other->name;
	}

	/** @return iterable<Node> */
	public function children(): iterable {
		return [];
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Name',
			'nameType' => 'TypeName',
			'name' => $this->name
		];
	}
}