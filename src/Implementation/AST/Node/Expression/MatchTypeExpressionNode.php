<?php

namespace Walnut\Lang\Implementation\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchExpressionDefaultNode as MatchExpressionDefaultNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchExpressionPairNode as MatchExpressionPairNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchTypeExpressionNode as MatchTypeExpressionNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;

final readonly class MatchTypeExpressionNode implements MatchTypeExpressionNodeInterface {
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
			'nodeName' => 'MatchTypeExpression',
			'target' => $this->target,
			'pairs' => $this->pairs
		];
	}
}