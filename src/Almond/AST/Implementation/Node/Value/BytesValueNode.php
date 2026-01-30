<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Value;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\BytesValueNode as BytesValueNodeInterface;

final readonly class BytesValueNode implements BytesValueNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public string $value
	) {}

	public function children(): iterable {
		return [];
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Value',
			'nodeName' => 'BytesValue',
			'value' => $this->value
		];
	}
}