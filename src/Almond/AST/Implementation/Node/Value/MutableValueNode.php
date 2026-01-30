<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Value;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\MutableValueNode as MutableValueNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\ValueNode;

final readonly class MutableValueNode implements MutableValueNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $type,
		public ValueNode $value,
	) {}

	public function children(): iterable {
		yield $this->type;
		yield $this->value;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Value',
			'nodeName' => 'MutableValue',
			'type' => $this->type,
			'value' => $this->value
		];
	}
}