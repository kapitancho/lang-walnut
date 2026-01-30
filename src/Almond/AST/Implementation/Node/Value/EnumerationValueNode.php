<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Value;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\EnumerationValueNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\EnumerationValueNode as EnumerationValueNodeInterface;

final readonly class EnumerationValueNode implements EnumerationValueNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameNode $name,
		public EnumerationValueNameNode $enumValue
	) {}

	public function children(): iterable {
		yield $this->name;
		yield $this->enumValue;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Value',
			'nodeName' => 'EnumerationValue',
			'name' => $this->name,
			'value' => $this->enumValue
		];
	}
}