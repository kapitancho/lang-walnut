<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\BooleanXorExpressionNode as BooleanXorExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class BooleanXorExpressionNode implements BooleanXorExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $first,
		public ExpressionNode $second
	) {}

	public function children(): iterable {
		yield $this->first;
		yield $this->second;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'BooleanXorExpression',
			'first' => $this->first,
			'second' => $this->second
		];
	}
}