<?php

namespace Walnut\Lang\Implementation\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\SequenceExpressionNode as SequenceExpressionNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;

final readonly class SequenceExpressionNode implements SequenceExpressionNodeInterface {
	/** @param list<ExpressionNode> $expressions */
	public function __construct(
		public SourceLocation $sourceLocation,
		public array $expressions
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'SequenceExpression',
			'expressions' => $this->expressions
		];
	}
}