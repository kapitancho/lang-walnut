<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ConstantExpressionNode as ConstantExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\ValueNode;

final readonly class ConstantExpressionNode implements ConstantExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ValueNode $value
	) {}

	public function children(): iterable {
		yield $this->value;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'ConstantExpression',
			'value' => $this->value
		];
	}
}