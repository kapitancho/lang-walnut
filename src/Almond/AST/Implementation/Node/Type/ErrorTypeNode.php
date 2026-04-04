<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\ErrorTypeNode as ErrorTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

final readonly class ErrorTypeNode implements ErrorTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $errorType
	) {}

	public function children(): iterable {
		yield $this->errorType;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'ErrorType',
			'valueType' => $this->errorType
		];
	}
}