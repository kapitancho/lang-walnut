<?php

namespace Walnut\Lang\Implementation\AST\Node\Value;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Value\RecordValueNode as RecordValueNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Value\ValueNode;

final readonly class RecordValueNode implements RecordValueNodeInterface {
	/** @param array<string, ValueNode> $values */
	public function __construct(
		public SourceLocation $sourceLocation,
		public array $values
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Value',
			'nodeName' => 'RecordValue',
			'values' => $this->values
		];
	}
}