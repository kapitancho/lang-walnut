<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MatchEmptyExpressionNode as MatchEmptyExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class MatchEmptyExpressionNode implements MatchEmptyExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $condition,
		public ExpressionNode $onEmpty,
		public ExpressionNode|null $else
	) {}

	public function children(): iterable {
		yield $this->condition;
		yield $this->onEmpty;
		if ($this->else !== null) {
			yield $this->else;
		}
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'MatchEmptyExpression',
			'condition' => $this->condition,
			'onEmpty' => $this->onEmpty,
			'else' => $this->else,
		];
	}
}