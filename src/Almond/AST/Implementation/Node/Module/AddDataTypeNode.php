<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Module;

use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddDataTypeNode as AddDataTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

final readonly class AddDataTypeNode implements AddDataTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameNode $name,
		public TypeNode $valueType
	) {}

	public function children(): iterable {
		yield $this->name;
		yield $this->valueType;
	}

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