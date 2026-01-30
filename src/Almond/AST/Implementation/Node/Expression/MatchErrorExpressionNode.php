<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MatchErrorExpressionNode as MatchErrorExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class MatchErrorExpressionNode implements MatchErrorExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $condition,
		public ExpressionNode $onError,
		public ExpressionNode|null $else
	) {}

	public function children(): iterable {
		yield $this->condition;
		yield $this->onError;
		if ($this->else !== null) {
			yield $this->else;
		}
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'MatchErrorExpression',
			'condition' => $this->condition,
			'onError' => $this->onError,
			'else' => $this->else,
		];
	}
}