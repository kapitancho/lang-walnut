<?php

namespace Walnut\Lang\Implementation\AST\Node\Value;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Value\DataValueNode as DataValueNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Value\ValueNode;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

final readonly class DataValueNode implements DataValueNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameIdentifier $name,
		public ValueNode $value,
	) {}

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