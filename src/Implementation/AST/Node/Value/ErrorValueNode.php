<?php

namespace Walnut\Lang\Implementation\AST\Node\Value;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Value\ErrorValueNode as ErrorValueNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Value\ValueNode;

final readonly class ErrorValueNode implements ErrorValueNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ValueNode $value,
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Value',
			'nodeName' => 'ErrorValue',
			'value' => $this->value
		];
	}
}