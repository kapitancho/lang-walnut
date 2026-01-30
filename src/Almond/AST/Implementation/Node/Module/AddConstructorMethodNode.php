<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Module;

use Walnut\Lang\Almond\AST\Blueprint\Node\FunctionBodyNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddConstructorMethodNode as AddConstructorMethodNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\NameAndTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Node;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

final readonly class AddConstructorMethodNode implements AddConstructorMethodNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameNode $typeName,
		public NameAndTypeNode $parameter,
		public NameAndTypeNode $dependency,
		public TypeNode $errorType,
		public FunctionBodyNode $functionBody
	) {}

	/** @return iterable<Node> */
	public function children(): iterable {
		yield $this->typeName;
		yield $this->parameter;
		yield $this->dependency;
		yield $this->errorType;
		yield $this->functionBody;
	}

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