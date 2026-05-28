<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\EmptySkipTargetExpressionNode as EmptySkipTargetExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class EmptySkipTargetExpressionNode implements EmptySkipTargetExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public string $skipTargetId,
		public ExpressionNode $targetExpression,
	) {}

	public function children(): iterable {
		yield $this->targetExpression;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'EmptySkipTargetExpression',
			'skipTargetId' => $this->skipTargetId,
			'targetExpression' => $this->targetExpression
		];
	}
}
