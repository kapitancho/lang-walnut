<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Value;

use Walnut\Lang\Almond\AST\Blueprint\Node\FunctionBodyNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\NameAndTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\FunctionValueNode as FunctionValueNodeInterface;

final readonly class FunctionValueNode implements FunctionValueNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public NameAndTypeNode $parameter,
		public NameAndTypeNode $dependency,
		public TypeNode $returnType,
		public FunctionBodyNode $functionBody
	) {}

	public function children(): iterable {
		yield $this->parameter;
		yield $this->dependency;
		yield $this->returnType;
		yield $this->functionBody;
	}

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