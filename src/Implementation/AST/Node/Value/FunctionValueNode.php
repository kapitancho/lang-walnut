<?php

namespace Walnut\Lang\Implementation\AST\Node\Value;

use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\AST\Node\Value\FunctionValueNode as FunctionValueNodeInterface;

final readonly class FunctionValueNode implements FunctionValueNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $parameterType,
		public TypeNode $dependencyType,
		public TypeNode $returnType,
		public FunctionBodyNode $functionBody
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Value',
			'nodeName' => 'FunctionValue',
			'parameterType' => $this->parameterType,
			'dependencyType' => $this->dependencyType,
			'returnType' => $this->returnType,
			'functionBody' => $this->functionBody
		];
	}
}