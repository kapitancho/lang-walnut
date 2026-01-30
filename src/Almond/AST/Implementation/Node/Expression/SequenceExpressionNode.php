<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\SequenceExpressionNode as SequenceExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class SequenceExpressionNode implements SequenceExpressionNodeInterface {
	/** @param list<ExpressionNode> $expressions */
	public function __construct(
		public SourceLocation $sourceLocation,
		public array $expressions
	) {}

	public function children(): iterable {
		yield from $this->expressions;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'SequenceExpression',
			'expressions' => $this->expressions
		];
	}
}