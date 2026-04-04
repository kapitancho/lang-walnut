<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Value;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\ValueValueNode as ValueValueNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\ValueNode;

final readonly class ValueValueNode implements ValueValueNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ValueNode $value,
	) {}

	public function children(): iterable {
		yield $this->value;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Value',
			'nodeName' => 'ValueValue',
			'value' => $this->value
		];
	}
}