<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\FunctionTypeNode as FunctionTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;

final readonly class FunctionTypeNode implements FunctionTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $parameterType,
		public TypeNode $returnType
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'FunctionType',
			'parameterType' => $this->parameterType,
			'returnType' => $this->returnType
		];
	}
}