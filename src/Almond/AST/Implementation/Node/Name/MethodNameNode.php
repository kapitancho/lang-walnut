<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Name;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\MethodNameNode as MethodNameNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Node;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class MethodNameNode implements MethodNameNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public string $name
	) {}


	/** @return iterable<Node> */
	public function children(): iterable {
		return [];
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Name',
			'nameType' => 'MethodName',
			'name' => $this->name
		];
	}
}