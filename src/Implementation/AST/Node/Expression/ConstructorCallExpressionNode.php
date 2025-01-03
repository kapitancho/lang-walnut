<?php

namespace Walnut\Lang\Implementation\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Expression\ConstructorCallExpressionNode as ConstructorCallExpressionNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

final readonly class ConstructorCallExpressionNode implements ConstructorCallExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameIdentifier $typeName,
		public ExpressionNode $parameter
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'ConstructorCallExpression',
			'typeName' => $this->typeName,
			'parameter' => $this->parameter
		];
	}
}