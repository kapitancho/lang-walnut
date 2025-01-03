<?php

namespace Walnut\Lang\Implementation\AST\Node\Value;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Value\StringValueNode as StringValueNodeInterface;

final readonly class StringValueNode implements StringValueNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public string $value
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Value',
			'nodeName' => 'StringValue',
			'value' => $this->value
		];
	}
}