<?php

namespace Walnut\Lang\Implementation\AST\Node\Module;

use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddMethodNode as AddMethodNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\NameAndTypeNode;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;

final readonly class AddMethodNode implements AddMethodNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $targetType,
		public MethodNameIdentifier $methodName,
		public NameAndTypeNode $parameter,
		public NameAndTypeNode $dependency,
		public TypeNode $returnType,
		public FunctionBodyNode $functionBody
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'ModuleDefinition',
			'nodeName' => 'AddMethod',
			'targetType' => $this->targetType,
			'methodName' => $this->methodName,
			'parameter' => $this->parameter,
			'dependency' => $this->dependency,
			'returnType' => $this->returnType,
			'functionBody' => $this->functionBody
		];
	}
}