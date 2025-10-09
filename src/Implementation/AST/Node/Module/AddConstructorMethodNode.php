<?php

namespace Walnut\Lang\Implementation\AST\Node\Module;

use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddConstructorMethodNode as AddConstructorMethodNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\NameAndTypeNode;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

final readonly class AddConstructorMethodNode implements AddConstructorMethodNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameIdentifier $typeName,
		public NameAndTypeNode $parameter,
		public NameAndTypeNode $dependency,
		public TypeNode $errorType,
		public FunctionBodyNode $functionBody
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'ModuleDefinition',
			'nodeName' => 'AddConstructor',
			'typeName' => $this->typeName,
			'parameter' => $this->parameter,
			'dependency' => $this->dependency,
			'errorType' => $this->errorType,
			'functionBody' => $this->functionBody
		];
	}
}