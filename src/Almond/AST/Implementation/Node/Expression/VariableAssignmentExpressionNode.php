<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\VariableAssignmentExpressionNode as VariableAssignmentExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\VariableNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class VariableAssignmentExpressionNode implements VariableAssignmentExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public VariableNameNode $variableName,
		public ExpressionNode $assignedExpression
	) {}

	public function children(): iterable {
		yield $this->variableName;
		yield $this->assignedExpression;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'VariableAssignmentExpression',
			'variableName' => $this->variableName,
			'assignedExpression' => $this->assignedExpression
		];
	}
}