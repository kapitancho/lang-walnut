<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Name;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\VariableNameNode as VariableNameNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Node;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class VariableNameNode implements VariableNameNodeInterface {
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
			'nameType' => 'VariableName',
			'name' => $this->name
		];
	}
}