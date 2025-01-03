<?php

namespace Walnut\Lang\Implementation\AST\Node\Value;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\AST\Node\Value\TypeValueNode as TypeValueNodeInterface;

final readonly class TypeValueNode implements TypeValueNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $type
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Value',
			'nodeName' => 'TypeValue',
			'type' => $this->type
		];
	}
}