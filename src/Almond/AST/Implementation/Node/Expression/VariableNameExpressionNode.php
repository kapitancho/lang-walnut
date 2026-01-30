<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\VariableNameExpressionNode as VariableNameExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\VariableNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class VariableNameExpressionNode implements VariableNameExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public VariableNameNode $variableName
	) {}

	public function children(): iterable {
		yield $this->variableName;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'VariableNameExpression',
			'variableName' => $this->variableName
		];
	}
}