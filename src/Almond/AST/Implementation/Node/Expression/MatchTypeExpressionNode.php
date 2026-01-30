<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MatchExpressionDefaultNode as MatchExpressionDefaultNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MatchExpressionPairNode as MatchExpressionPairNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MatchTypeExpressionNode as MatchTypeExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class MatchTypeExpressionNode implements MatchTypeExpressionNodeInterface {
	/** @param list<MatchExpressionPairNodeInterface> $pairs */
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $target,
		public array $pairs,
		public MatchExpressionDefaultNodeInterface|null $default
	) {}

	public function children(): iterable {
		yield $this->target;
		yield from $this->pairs;
		if ($this->default !== null) {
			yield $this->default;
		}
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'MatchTypeExpression',
			'target' => $this->target,
			'pairs' => $this->pairs,
			'default' => $this->default
		];
	}
}