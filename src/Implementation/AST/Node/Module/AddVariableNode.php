<?php

namespace Walnut\Lang\Implementation\AST\Node\Module;

use Walnut\Lang\Blueprint\AST\Node\Module\AddVariableNode as AddVariableNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Value\ValueNode;
use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;

final readonly class AddVariableNode implements AddVariableNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public VariableNameIdentifier $name,
		public ValueNode $value
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'ModuleDefinition',
			'nodeName' => 'AddVariable',
			'name' => $this->name,
			'value' => $this->value
		];
	}
}