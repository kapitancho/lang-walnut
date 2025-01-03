<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\ResultTypeNode as ResultTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;

final readonly class ResultTypeNode implements ResultTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $returnType,
		public TypeNode $errorType,
	) {}

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