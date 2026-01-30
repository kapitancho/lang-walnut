<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Module;

use Walnut\Lang\Almond\AST\Blueprint\Node\FunctionBodyNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddMethodNode as AddMethodNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\MethodNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\NameAndTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Node;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

final readonly class AddMethodNode implements AddMethodNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameNode $targetType,
		public MethodNameNode $methodName,
		public NameAndTypeNode $parameter,
		public NameAndTypeNode $dependency,
		public TypeNode $returnType,
		public FunctionBodyNode $functionBody
	) {}

	/** @return iterable<Node> */
	public function children(): iterable {
		yield $this->targetType;
		yield $this->methodName;
		yield $this->parameter;
		yield $this->dependency;
		yield $this->returnType;
		yield $this->functionBody;
	}

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