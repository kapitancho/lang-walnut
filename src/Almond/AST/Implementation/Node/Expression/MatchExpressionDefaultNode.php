<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MatchExpressionDefaultNode as MatchExpressionDefaultNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class MatchExpressionDefaultNode implements MatchExpressionDefaultNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $valueExpression
	) {}

	public function children(): iterable {
		yield $this->valueExpression;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'MatchExpressionDefault',
			'valueExpression' => $this->valueExpression
		];
	}
}