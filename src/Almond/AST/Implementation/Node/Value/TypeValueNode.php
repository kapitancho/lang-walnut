<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Value;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\TypeValueNode as TypeValueNodeInterface;

final readonly class TypeValueNode implements TypeValueNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $type
	) {}

	public function children(): iterable {
		yield $this->type;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Value',
			'nodeName' => 'TypeValue',
			'type' => $this->type
		];
	}
}