<?php

namespace Walnut\Lang\Implementation\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\RecordExpressionNode as RecordExpressionNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;

final readonly class RecordExpressionNode implements RecordExpressionNodeInterface {
	/** @param array<string, ExpressionNode> $values */
	public function __construct(
		public SourceLocation $sourceLocation,
		public array $values
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'RecordExpression',
			'values' => $this->values
		];
	}
}