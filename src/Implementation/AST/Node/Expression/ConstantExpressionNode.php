<?php

namespace Walnut\Lang\Implementation\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Expression\ConstantExpressionNode as ConstantExpressionNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Value\ValueNode;

final readonly class ConstantExpressionNode implements ConstantExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ValueNode $value
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'ConstantExpression',
			'value' => $this->value
		];
	}
}