<?php

namespace Walnut\Lang\Implementation\AST\Node\Module;

use Walnut\Lang\Blueprint\AST\Node\Module\AddDataTypeNode as AddDataTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

final readonly class AddDataTypeNode implements AddDataTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameIdentifier $name,
		public TypeNode $valueType
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'ModuleDefinition',
			'nodeName' => 'AddDataType',
			'name' => $this->name,
			'valueType' => $this->valueType,
		];
	}
}