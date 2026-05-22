<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\EmptyAsErrorExpressionNode as EmptyAsErrorExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class EmptyAsErrorExpressionNode implements EmptyAsErrorExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $targetExpression,
		public ExpressionNode $errorExpression
	) {}

	public function children(): iterable {
		yield $this->targetExpression;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'EmptyAsErrorExpression',
			'targetExpression' => $this->targetExpression,
			'errorExpression' => $this->errorExpression,
		];
	}
}