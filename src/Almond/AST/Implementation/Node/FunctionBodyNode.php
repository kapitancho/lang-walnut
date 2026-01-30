<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\FunctionBodyNode as FunctionBodyNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class FunctionBodyNode implements FunctionBodyNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $expression
	) {}

	public function children(): iterable {
		yield $this->expression;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'FunctionBody',
			'nodeName' => 'FunctionBody',
			'expression' => $this->expression
		];
	}
}