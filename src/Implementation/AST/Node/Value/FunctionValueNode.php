<?php

namespace Walnut\Lang\Implementation\AST\Node\Value;

use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\AST\Node\Value\FunctionValueNode as FunctionValueNodeInterface;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;

final readonly class FunctionValueNode implements FunctionValueNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $parameterType,
		public VariableNameIdentifier|null $parameterName,
		public TypeNode $dependencyType,
		public VariableNameIdentifier|null $dependencyName,
		public TypeNode $returnType,
		public FunctionBodyNode $functionBody
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Value',
			'nodeName' => 'FunctionValue',
			'parameterType' => $this->parameterType,
			'parameterName' => $this->parameterName,
			'dependencyType' => $this->dependencyType,
			'dependencyName' => $this->dependencyName,
			'returnType' => $this->returnType,
			'functionBody' => $this->functionBody
		];
	}
}