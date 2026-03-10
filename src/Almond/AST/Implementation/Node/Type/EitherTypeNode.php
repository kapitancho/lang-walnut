<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\EitherTypeNode as EitherTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

final readonly class EitherTypeNode implements EitherTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode       $valueType,
		public TypeNode       $errorType,
	) {}

	public function children(): iterable {
		yield $this->valueType;
		yield $this->errorType;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'EitherType',
			'valueType' => $this->valueType,
			'errorType' => $this->errorType
		];
	}
}