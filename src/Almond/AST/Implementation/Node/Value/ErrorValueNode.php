<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Value;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\ErrorValueNode as ErrorValueNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\ValueNode;

final readonly class ErrorValueNode implements ErrorValueNodeInterface {
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
			'nodeName' => 'ErrorValue',
			'value' => $this->value
		];
	}
}