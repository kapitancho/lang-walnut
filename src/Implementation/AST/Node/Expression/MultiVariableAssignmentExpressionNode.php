<?php

namespace Walnut\Lang\Implementation\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MultiVariableAssignmentExpressionNode as MultiVariableAssignmentExpressionNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;

final readonly class MultiVariableAssignmentExpressionNode implements MultiVariableAssignmentExpressionNodeInterface {

	/** @param array<VariableNameIdentifier> $variableNames */
	public function __construct(
		public SourceLocation $sourceLocation,
		public array $variableNames,
		public ExpressionNode $assignedExpression
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'VariableAssignmentExpression',
			'variableNames' => $this->variableNames,
			'assignedExpression' => $this->assignedExpression
		];
	}
}