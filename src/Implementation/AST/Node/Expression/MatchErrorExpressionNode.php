<?php

namespace Walnut\Lang\Implementation\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchErrorExpressionNode as MatchErrorExpressionNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;

final readonly class MatchErrorExpressionNode implements MatchErrorExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $condition,
		public ExpressionNode $onError,
		public ExpressionNode|null $else
	) {}

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