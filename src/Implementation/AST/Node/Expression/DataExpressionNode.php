<?php

namespace Walnut\Lang\Implementation\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Expression\DataExpressionNode as DataExpressionNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

final readonly class DataExpressionNode implements DataExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameIdentifier $typeName,
		public ExpressionNode $value
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'DataExpression',
			'typeName' => $this->typeName,
			'parameter' => $this->value
		];
	}
}