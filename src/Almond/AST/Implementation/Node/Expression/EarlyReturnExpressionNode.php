<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\EarlyReturnExpressionNode as EarlyReturnExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\EarlyReturnExpressionType;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class EarlyReturnExpressionNode implements EarlyReturnExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public ExpressionNode $targetExpression,
		public EarlyReturnExpressionType $type
	) {}

	public function children(): iterable {
		yield $this->targetExpression;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'EarlyReturnExpression',
			'type' => $this->type->name,
			'targetExpression' => $this->targetExpression
		];
	}
}
