<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\MutableTypeNode as MutableTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

final readonly class MutableTypeNode implements MutableTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $valueType
	) {}

	public function children(): iterable {
		yield $this->valueType;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'MutableType',
			'valueType' => $this->valueType
		];
	}
}