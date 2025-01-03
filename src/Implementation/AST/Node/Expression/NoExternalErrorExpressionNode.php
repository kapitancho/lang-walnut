<?php

namespace Walnut\Lang\Implementation\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\NoExternalErrorExpressionNode as NoExternalErrorExpressionNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;

final readonly class NoExternalErrorExpressionNode implements NoExternalErrorExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $targetExpression
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'NoExternalErrorExpression',
			'targetExpression' => $this->targetExpression
		];
	}
}