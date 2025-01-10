<?php

namespace Walnut\Lang\Implementation\AST\Node\Module;

use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddSubtypeTypeNode as AddSubtypeTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

final readonly class AddSubtypeTypeNode implements AddSubtypeTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameIdentifier $name,
		public TypeNode $baseType,
		public FunctionBodyNode $constructorBody,
		public TypeNode $errorType,
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'ModuleDefinition',
			'nodeName' => 'AddSubtypeType',
			'name' => $this->name,
			'baseType' => $this->baseType,
			'constructorBody' => $this->constructorBody,
			'errorType' => $this->errorType
		];
	}
}