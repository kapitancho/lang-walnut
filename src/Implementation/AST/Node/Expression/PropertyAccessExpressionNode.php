<?php

namespace Walnut\Lang\Implementation\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\PropertyAccessExpressionNode as PropertyAccessExpressionNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;

final readonly class PropertyAccessExpressionNode implements PropertyAccessExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $target,
		public string|int $propertyName,
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'PropertyAccessExpression',
			'target' => $this->target,
			'propertyName' => $this->propertyName
		];
	}
}