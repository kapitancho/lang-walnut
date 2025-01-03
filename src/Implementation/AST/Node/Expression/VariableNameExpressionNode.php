<?php

namespace Walnut\Lang\Implementation\AST\Node\Expression;

use Walnut\Lang\Blueprint\AST\Node\Expression\VariableNameExpressionNode as VariableNameExpressionNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;

final readonly class VariableNameExpressionNode implements VariableNameExpressionNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public VariableNameIdentifier $variableName
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Expression',
			'nodeName' => 'VariableNameExpression',
			'variableName' => $this->variableName
		];
	}
}