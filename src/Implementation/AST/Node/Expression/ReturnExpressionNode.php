<?php

namespace Walnut\Lang\Implementation\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\ReturnExpressionNode as ReturnExpressionNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;

final readonly class ReturnExpressionNode implements ReturnExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $returnedExpression
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'ReturnExpression',
			'returnedExpression' => $this->returnedExpression
		];
	}
}