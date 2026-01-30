<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\FunctionTypeNode as FunctionTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

final readonly class FunctionTypeNode implements FunctionTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $parameterType,
		public TypeNode $returnType
	) {}

	public function children(): iterable {
		yield $this->parameterType;
		yield $this->returnType;
	}

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