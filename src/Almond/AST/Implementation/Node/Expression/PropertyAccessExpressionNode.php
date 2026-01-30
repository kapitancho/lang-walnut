<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\PropertyAccessExpressionNode as PropertyAccessExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class PropertyAccessExpressionNode implements PropertyAccessExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $target,
		public string|int $propertyName,
	) {}

	public function children(): iterable {
		yield $this->target;
	}

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