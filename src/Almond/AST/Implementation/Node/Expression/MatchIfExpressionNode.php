<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MatchIfExpressionNode as MatchIfExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class MatchIfExpressionNode implements MatchIfExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $condition,
		public ExpressionNode $then,
		public ExpressionNode $else
	) {}

	public function children(): iterable {
		yield $this->condition;
		yield $this->then;
		yield $this->else;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'MatchIfExpression',
			'condition' => $this->condition,
			'then' => $this->then,
			'else' => $this->else,
		];
	}
}