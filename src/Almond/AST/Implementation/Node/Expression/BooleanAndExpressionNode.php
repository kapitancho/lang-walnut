<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\BooleanAndExpressionNode as BooleanAndExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class BooleanAndExpressionNode implements BooleanAndExpressionNodeInterface {
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
			'nodeName' => 'BooleanAndExpression',
			'first' => $this->first,
			'second' => $this->second
		];
	}
}