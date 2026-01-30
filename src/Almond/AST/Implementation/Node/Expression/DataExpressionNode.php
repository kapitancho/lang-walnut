<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\DataExpressionNode as DataExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class DataExpressionNode implements DataExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameNode $typeName,
		public ExpressionNode $value
	) {}

	public function children(): iterable {
		yield $this->typeName;
		yield $this->value;
	}

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