<?php

namespace Walnut\Lang\Implementation\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MethodCallExpressionNode as MethodCallExpressionNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;

final readonly class MethodCallExpressionNode implements MethodCallExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $target,
		public MethodNameIdentifier $methodName,
		public ExpressionNode $parameter
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'MethodCallExpression',
			'target' => $this->target,
			'methodName' => $this->methodName,
			'parameter' => $this->parameter
		];
	}
}