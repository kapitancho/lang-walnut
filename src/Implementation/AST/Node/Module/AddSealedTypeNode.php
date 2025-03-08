<?php

namespace Walnut\Lang\Implementation\AST\Node\Module;

use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddSealedTypeNode as AddSealedTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

final readonly class AddSealedTypeNode implements AddSealedTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameIdentifier $name,
		public TypeNode $valueType,
		public FunctionBodyNode|null $constructorBody,
		public TypeNode|null $errorType,
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'ModuleDefinition',
			'nodeName' => 'AddSealedType',
			'name' => $this->name,
			'valueType' => $this->valueType,
			'constructorBody' => $this->constructorBody,
			'errorType' => $this->errorType
		];
	}
}