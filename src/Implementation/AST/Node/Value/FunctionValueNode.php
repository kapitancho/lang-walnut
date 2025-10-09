<?php

namespace Walnut\Lang\Implementation\AST\Node\Value;

use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\AST\Node\NameAndTypeNode;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\AST\Node\Value\FunctionValueNode as FunctionValueNodeInterface;

final readonly class FunctionValueNode implements FunctionValueNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public NameAndTypeNode $parameter,
		public NameAndTypeNode $dependency,
		public TypeNode $returnType,
		public FunctionBodyNode $functionBody
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Value',
			'nodeName' => 'FunctionValue',
			'parameter' => $this->parameter,
			'dependency' => $this->dependency,
			'returnType' => $this->returnType,
			'functionBody' => $this->functionBody
		];
	}
}