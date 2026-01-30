<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeTypeNode as TypeTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

final readonly class TypeTypeNode implements TypeTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $refType
	) {}

	public function children(): iterable {
		yield $this->refType;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'TypeType',
			'refType' => $this->refType
		];
	}
}