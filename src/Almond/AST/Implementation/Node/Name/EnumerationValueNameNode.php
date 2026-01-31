<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Name;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\EnumerationValueNameNode as EnumerationValueNameNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Node;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class EnumerationValueNameNode implements EnumerationValueNameNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public string $name
	) {}

	public function equals(EnumerationValueNameNodeInterface $other): bool {
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
			'nameType' => 'EnumerationValueName',
			'name' => $this->name
		];
	}
}