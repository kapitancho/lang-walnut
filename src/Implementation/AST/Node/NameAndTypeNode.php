<?php

namespace Walnut\Lang\Implementation\AST\Node;

use Walnut\Lang\Blueprint\AST\Node\NameAndTypeNode as NameAndTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;

final readonly class NameAndTypeNode implements NameAndTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $type,
		public VariableNameIdentifier|null $name
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'TypeAndName',
			'nodeName' => 'TypeAndName',
			'type' => $this->type,
			'name' => $this->name
		];
	}
}