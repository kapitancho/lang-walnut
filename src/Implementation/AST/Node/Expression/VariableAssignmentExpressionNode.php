<?php

namespace Walnut\Lang\Implementation\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\VariableAssignmentExpressionNode as VariableAssignmentExpressionNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;

final readonly class VariableAssignmentExpressionNode implements VariableAssignmentExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public VariableNameIdentifier $variableName,
		public ExpressionNode $assignedExpression
	) {}

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