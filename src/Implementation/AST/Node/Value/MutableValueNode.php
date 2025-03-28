<?php

namespace Walnut\Lang\Implementation\AST\Node\Value;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\AST\Node\Value\MutableValueNode as MutableValueNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Value\ValueNode;

final readonly class MutableValueNode implements MutableValueNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $type,
		public ValueNode $value,
	) {}

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