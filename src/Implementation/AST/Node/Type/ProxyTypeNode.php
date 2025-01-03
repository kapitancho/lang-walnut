<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\ProxyTypeNode as ProxyTypeNodeInterface;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;

final readonly class ProxyTypeNode implements ProxyTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameIdentifier $name
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'ProxyType',
			'name' => $this->name
		];
	}
}