<?php

namespace Walnut\Lang\Implementation\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchExpressionDefaultNode as MatchExpressionDefaultNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchExpressionPairNode as MatchExpressionPairNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchValueExpressionNode as MatchValueExpressionNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;

final readonly class MatchValueExpressionNode implements MatchValueExpressionNodeInterface {
	/** @param list<MatchExpressionPairNodeInterface|MatchExpressionDefaultNodeInterface> $pairs */
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $target,
		public array $pairs
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'MatchValueExpression',
			'target' => $this->target,
			'pairs' => $this->pairs
		];
	}
}