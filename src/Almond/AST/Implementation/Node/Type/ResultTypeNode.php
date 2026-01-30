<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\ResultTypeNode as ResultTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

final readonly class ResultTypeNode implements ResultTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $returnType,
		public TypeNode $errorType,
	) {}

	public function children(): iterable {
		yield $this->returnType;
		yield $this->errorType;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'ResultType',
			'returnType' => $this->returnType,
			'errorType' => $this->errorType
		];
	}
}