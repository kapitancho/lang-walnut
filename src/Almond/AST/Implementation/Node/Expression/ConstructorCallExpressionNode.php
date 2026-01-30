<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ConstructorCallExpressionNode as ConstructorCallExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class ConstructorCallExpressionNode implements ConstructorCallExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameNode $typeName,
		public ExpressionNode $parameter
	) {}

	public function children(): iterable {
		yield $this->typeName;
		yield $this->parameter;
	}


	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'ConstructorCallExpression',
			'typeName' => $this->typeName,
			'parameter' => $this->parameter
		];
	}
}