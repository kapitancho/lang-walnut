<?php

namespace Walnut\Lang\Implementation\AST\Node\Module;

use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddMethodNode as AddMethodNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;

final readonly class AddMethodNode implements AddMethodNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $targetType,
		public MethodNameIdentifier $methodName,
		public TypeNode $parameterType,
		public TypeNode $dependencyType,
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
			'parameterType' => $this->parameterType,
			'dependencyType' => $this->dependencyType,
			'returnType' => $this->returnType,
			'functionBody' => $this->functionBody
		];
	}
}