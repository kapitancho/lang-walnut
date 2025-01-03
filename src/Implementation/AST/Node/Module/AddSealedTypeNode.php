<?php

namespace Walnut\Lang\Implementation\AST\Node\Module;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddSealedTypeNode as AddSealedTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\RecordTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;

final readonly class AddSealedTypeNode implements AddSealedTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameIdentifier $name,
		public RecordTypeNode $valueType,
		public ExpressionNode $constructorBody,
		public TypeNode $errorType,
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