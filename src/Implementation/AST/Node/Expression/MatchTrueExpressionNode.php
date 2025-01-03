<?php

namespace Walnut\Lang\Implementation\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Expression\MatchExpressionDefaultNode as MatchExpressionDefaultNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchExpressionPairNode as MatchExpressionPairNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchTrueExpressionNode as MatchTrueExpressionNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;

final readonly class MatchTrueExpressionNode implements MatchTrueExpressionNodeInterface {
	/** @param list<MatchExpressionPairNodeInterface|MatchExpressionDefaultNodeInterface> $pairs */
	public function __construct(
		public SourceLocation $sourceLocation,
		public array $pairs
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'MatchTrueExpression',
			'pairs' => $this->pairs
		];
	}
}