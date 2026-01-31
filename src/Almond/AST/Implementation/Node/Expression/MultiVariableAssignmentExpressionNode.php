<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Expression;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MultiVariableAssignmentExpressionNode as MultiVariableAssignmentExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\VariableNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class MultiVariableAssignmentExpressionNode implements MultiVariableAssignmentExpressionNodeInterface {

	/** @param array<VariableNameNode> $variableNames */
	public function __construct(
		public SourceLocation $sourceLocation,
		public array $variableNames,
		public ExpressionNode $assignedExpression
	) {}

	public function children(): iterable {
		yield from $this->variableNames;
		yield $this->assignedExpression;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'MultiVariableAssignmentExpression',
			'variableNames' => $this->variableNames,
			'assignedExpression' => $this->assignedExpression
		];
	}
}