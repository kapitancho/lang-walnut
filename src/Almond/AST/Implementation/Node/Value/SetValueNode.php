<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Value;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\SetValueNode as SetValueNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\ValueNode;

final readonly class SetValueNode implements SetValueNodeInterface {
	/** @param list<ValueNode> $values */
	public function __construct(
		public SourceLocation $sourceLocation,
		public array $values
	) {}

	public function children(): iterable {
		yield from $this->values;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Value',
			'nodeName' => 'SetValue',
			'values' => $this->values
		];
	}
}