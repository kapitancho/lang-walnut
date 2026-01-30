<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Value;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\AtomValueNode as AtomValueNodeInterface;

final readonly class AtomValueNode implements AtomValueNodeInterface {
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
			'nodeCategory' => 'Value',
			'nodeName' => 'AtomValue',
			'name' => $this->name
		];
	}
}