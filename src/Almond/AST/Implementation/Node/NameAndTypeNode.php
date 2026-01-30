<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\VariableNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\NameAndTypeNode as NameAndTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Node;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

final readonly class NameAndTypeNode implements NameAndTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $type,
		public VariableNameNode|null $name
	) {}


	/** @return iterable<Node> */
	public function children(): iterable {
		//TODO: $name
		yield $this->type;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'TypeAndName',
			'nodeName' => 'TypeAndName',
			'type' => $this->type,
			'name' => $this->name
		];
	}
}