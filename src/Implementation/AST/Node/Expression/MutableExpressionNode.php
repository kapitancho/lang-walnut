<?php

namespace Walnut\Lang\Implementation\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MutableExpressionNode as MutableExpressionNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;

final readonly class MutableExpressionNode implements MutableExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $type,
		public ExpressionNode $value
	) {}

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