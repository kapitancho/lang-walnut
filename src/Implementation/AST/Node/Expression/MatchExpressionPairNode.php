<?php

namespace Walnut\Lang\Implementation\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchExpressionPairNode as MatchExpressionPairNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;

final readonly class MatchExpressionPairNode implements MatchExpressionPairNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $matchExpression,
		public ExpressionNode $valueExpression
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'MatchExpressionPair',
			'matchExpression' => $this->matchExpression,
			'valueExpression' => $this->valueExpression
		];
	}
}