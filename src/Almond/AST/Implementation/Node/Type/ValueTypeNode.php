<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\ValueTypeNode as ValueTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

final readonly class ValueTypeNode implements ValueTypeNodeInterface {
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
			'nodeName' => 'ValueType',
			'valueType' => $this->valueType
		];
	}
}