<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MatchExternalErrorExpressionNode as MatchExternalErrorExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class MatchExternalErrorExpressionNode implements MatchExternalErrorExpressionNodeInterface {
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
			'nodeName' => 'MatchExternalErrorExpression',
			'condition' => $this->condition,
			'onError' => $this->onError,
			'else' => $this->else,
		];
	}
}