<?php

namespace Walnut\Lang\Implementation\AST\Node\Value;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Value\BytesValueNode as BytesValueNodeInterface;

final readonly class BytesValueNode implements BytesValueNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public string $value
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Value',
			'nodeName' => 'BytesValue',
			'value' => $this->value
		];
	}
}