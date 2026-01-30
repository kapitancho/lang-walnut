<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\GroupExpressionNode as GroupExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class GroupExpressionNode implements GroupExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $innerExpression
	) {}

	public function children(): iterable {
		yield $this->innerExpression;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'GroupExpression',
			'innerExpression' => $this->innerExpression
		];
	}
}