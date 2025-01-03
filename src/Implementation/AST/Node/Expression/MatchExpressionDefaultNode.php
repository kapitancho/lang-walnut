<?php

namespace Walnut\Lang\Implementation\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchExpressionDefaultNode as MatchExpressionDefaultNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;

final readonly class MatchExpressionDefaultNode implements MatchExpressionDefaultNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $valueExpression
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'MatchExpressionDefault',
			'valueExpression' => $this->valueExpression
		];
	}
}