<?php

namespace Walnut\Lang\Implementation\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchIfExpressionNode as MatchIfExpressionNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;

final readonly class MatchIfExpressionNode implements MatchIfExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $condition,
		public ExpressionNode $then,
		public ExpressionNode $else
	) {}

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