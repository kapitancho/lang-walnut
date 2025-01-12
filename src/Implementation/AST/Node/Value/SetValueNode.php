<?php

namespace Walnut\Lang\Implementation\AST\Node\Value;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Value\SetValueNode as SetValueNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Value\ValueNode;

final readonly class SetValueNode implements SetValueNodeInterface {
	/** @param list<ValueNode> $values */
	public function __construct(
		public SourceLocation $sourceLocation,
		public array $values
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Value',
			'nodeName' => 'SetValue',
			'values' => $this->values
		];
	}
}