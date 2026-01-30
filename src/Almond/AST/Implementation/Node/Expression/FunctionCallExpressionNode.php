<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\FunctionCallExpressionNode as FunctionCallExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class FunctionCallExpressionNode implements FunctionCallExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $target,
		public ExpressionNode $parameter
	) {}

	public function children(): iterable {
		yield $this->target;
		yield $this->parameter;
	}

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