<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Value;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\StringValueNode as StringValueNodeInterface;

final readonly class StringValueNode implements StringValueNodeInterface {
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
			'nodeName' => 'StringValue',
			'value' => $this->value
		];
	}
}