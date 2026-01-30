<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Value;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\DataValueNode as DataValueNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\ValueNode;

final readonly class DataValueNode implements DataValueNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameNode $name,
		public ValueNode $value,
	) {}

	public function children(): iterable {
		yield $this->name;
		yield $this->value;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Value',
			'nodeName' => 'DataValue',
			'name' => $this->name,
			'value' => $this->value
		];
	}
}