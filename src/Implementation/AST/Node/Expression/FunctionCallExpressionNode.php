<?php

namespace Walnut\Lang\Implementation\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\FunctionCallExpressionNode as FunctionCallExpressionNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;

final readonly class FunctionCallExpressionNode implements FunctionCallExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $target,
		public ExpressionNode $parameter
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'FunctionCallExpression',
			'target' => $this->target,
			'parameter' => $this->parameter
		];
	}
}