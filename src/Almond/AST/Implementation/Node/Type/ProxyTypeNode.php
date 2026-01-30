<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\ProxyTypeNode as ProxyTypeNodeInterface;

final readonly class ProxyTypeNode implements ProxyTypeNodeInterface {
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
			'nodeName' => 'ProxyType',
			'name' => $this->name
		];
	}
}