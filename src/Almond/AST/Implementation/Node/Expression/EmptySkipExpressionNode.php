<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\EmptySkipExpressionNode as EmptySkipExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class EmptySkipExpressionNode implements EmptySkipExpressionNodeInterface {
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
			'nodeName' => 'EmptySkipExpression',
			'skipTargetId' => $this->skipTargetId,
			'targetExpression' => $this->targetExpression
		];
	}
}
