<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MutableExpressionNode as MutableExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

final readonly class MutableExpressionNode implements MutableExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $type,
		public ExpressionNode $value
	) {}

	public function children(): iterable {
		yield $this->type;
		yield $this->value;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'MutableExpression',
			'type' => $this->type,
			'value' => $this->value
		];
	}
}