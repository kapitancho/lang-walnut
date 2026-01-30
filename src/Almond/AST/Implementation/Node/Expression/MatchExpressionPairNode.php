<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MatchExpressionPairNode as MatchExpressionPairNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class MatchExpressionPairNode implements MatchExpressionPairNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $matchExpression,
		public ExpressionNode $valueExpression
	) {}

	public function children(): iterable {
		yield $this->matchExpression;
		yield $this->valueExpression;
	}

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