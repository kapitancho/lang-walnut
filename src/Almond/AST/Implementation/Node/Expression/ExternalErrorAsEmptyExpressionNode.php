<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExternalErrorAsEmptyExpressionNode as ExternalErrorAsEmptyExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class ExternalErrorAsEmptyExpressionNode implements ExternalErrorAsEmptyExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $targetExpression
	) {}

	public function children(): iterable {
		yield $this->targetExpression;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'ExternalErrorAsEmptyExpression',
			'targetExpression' => $this->targetExpression
		];
	}
}